<?php

namespace app\controllers;

use app\components\GeneralChargeBonus;
use app\Environment;
use app\models\ChargeRequestHistory;
use app\models\ChargeSource;
use app\models\exceptions\InternalServerErrorHttpException;
use app\models\exceptions\UnauthorizedHttpException;
use app\models\forms\ChargePriceForm;
use app\models\charge_source_cooperation\ChargeSourceCooperation;
use app\models\PolletUser;
use Yii;
use yii\web\BadRequestHttpException;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * Class ChargeController
 * @package app\controllers
 */
class ChargeController extends CommonController
{
    const PRICE_MODE_FIRST  = 'first';
    const PRICE_MODE_NORMAL = 'charge';
    const POINT_SITE_AUTH_STATE_SESSION_KEY_BASE = 'point-site-auth-state';

    /**
     * 認証からのリダイレクト先
     * @return Response
     * @throws BadRequestHttpException
     * @throws HttpException
     */
    public function actionIndex()
    {
        $mode = $this->checkModeActionIndex();
        if ($mode === false) {
            return $this->goHome();
        }

        // 初回チャージの場合は「初回サイト認証完了済」にステータス変更
        if ($this->authorizedUser->isNewUser()) {
            $this->authorizedUser->updateStatus(PolletUser::STATUS_SITE_AUTHENTICATED);
        }

        return $this->redirect(['charge/price',
            'code' => Yii::$app->request->get('charge_source_code'),
            'mode' => $mode,
        ]);
    }

    /**
     * 30. チャージ金額確認
     * 31. チャージ金額選択
     * @param string $code 提携サイトCode
     * @param string $mode 初回チャージ('first') or 通常チャージ('charge')
     * @return string
     * @throws UnauthorizedHttpException
     */
    public function actionPrice($code = '', $mode = self::PRICE_MODE_NORMAL)
    {
        if (!$this->checkAccessableActionPrice($mode)) {
            throw new UnauthorizedHttpException();
        }

        $chargeSource = ChargeSource::find()->joinAuthorized()->andWhere([
            ChargeSource::tableName() . '.charge_source_code' => $code,
        ])->one();
        if (!$chargeSource || $chargeSource->requiresAuthorization() && !$chargeSource->isAuthorized) {
            throw new UnauthorizedHttpException();
        }

        if ($mode !== self::PRICE_MODE_FIRST) {
            //初回チャージ以外はカード発行手数料はかからない
            $chargeSource->card_issue_fee = 0;
        }

        $chargeRemain = $chargeSource->myValidPoint - $chargeSource->card_issue_fee;

        $formModel = new ChargePriceForm();
        $formModel->chargeRemain = $chargeRemain;
        $formModel->minValue     = $chargeSource->min_value;
        $formModel->cardIssueFee = $chargeSource->card_issue_fee;

        return $this->render('price', [
            'mode'         => $mode,
            'formModel'    => $formModel,
            'chargeSource' => $chargeSource,
            'chargeRemain' => $chargeRemain,
            'isFirst'      => $mode === self::PRICE_MODE_FIRST,
        ]);
    }

    /**
     * AJAX経由でボーナス額を取得するアクション
     *
     * POST値
     * {price} = 入力額 + 手数料
     *
     * 戻り値
     * 正常に終了した場合HTTP/200を返す。失敗時はHTTP/400
     *
     * @return integer
     * @throws BadRequestHttpException
     * @throws InternalServerErrorHttpException
     */
    public function actionBonusPrice()
    {
        if (!$this->checkAccessableActionPriceRequest()) {
            throw new InternalServerErrorHttpException();
        }

        /** @var $inputPrice integer|null */
        $inputPrice = Yii::$app->request->post('price');

        if (!$inputPrice) {
            throw new BadRequestHttpException('必要なパラメータがありません');
        }
        return GeneralChargeBonus::getPrice($inputPrice);
    }

    /**
     * AJAX経由でチャージ額申請を実行させるアクション
     *
     * POST値
     * {charge_source_code} = ポイントサイトCode
     * {price} = 入力金額（ボーナスや手数料を含まない）
     *
     * 戻り値
     * 正常に終了した場合HTTP/200を返す。失敗時はHTTP/400
     *
     * @return string
     * @throws BadRequestHttpException
     * @throws InternalServerErrorHttpException
     */
    public function actionPriceRequest()
    {
        if (!$this->checkAccessableActionPriceRequest()) {
            throw new InternalServerErrorHttpException();
        }

        /** @var $chargeSourceCode string|null */
        $chargeSourceCode = Yii::$app->request->post('charge_source_code');
        /** @var $inputPrice integer|null */
        $inputPrice = Yii::$app->request->post('price');

        if (!$chargeSourceCode || !$inputPrice) {
            throw new BadRequestHttpException('必要なパラメータがありません');
        }

        $chargeSource = ChargeSource::find()->joinAuthorized()->andWhere([
            ChargeSource::tableName() . '.charge_source_code' => $chargeSourceCode
        ])->one();
        if (!$chargeSource || $chargeSource->requiresAuthorization() && !$chargeSource->isAuthorized) {
            throw new BadRequestHttpException('パラメータが不正です');
        }

        // 初回チャージ時のみカード発行手数料がかかる
        $cardIssueFee = $this->authorizedUser->isSiteAuthenticated() ? $chargeSource->card_issue_fee : 0;
        $withdrawPrice = $inputPrice + $cardIssueFee;
        $generalChargeBonus = new GeneralChargeBonus();
        $chargePrice = $generalChargeBonus->applyTo($withdrawPrice) - $cardIssueFee;

        $trans = Yii::$app->db->beginTransaction();
        try {
            if ($this->authorizedUser->isSiteAuthenticated()) {
                // ユーザの登録状態が「サイト認証済み」状態ではまだチャージできない
                $status = ChargeRequestHistory::STATUS_UNPROCESSED_FIRST_CHARGE;
            } else {
                $status = ChargeRequestHistory::STATUS_READY;
            }
            $chargeRequestHistory = ChargeRequestHistory::add(
                $chargeSource,
                $this->authorizedUser,
                $chargePrice,
                $withdrawPrice,
                $status
            );

            $success = ChargeSourceCooperation::withdrawCash(
                $chargeSource,
                $this->authorizedUser,
                $withdrawPrice,
                $chargeRequestHistory->id
            );
            if (!$success) {
                throw new \Exception('failed point-site-cooperation exchange');
            }

            // 初回チャージであれば、'初回チャージ済'ステータスに更新
            if ($this->authorizedUser->isSiteAuthenticated()) {
                $this->authorizedUser->registration_status = PolletUser::STATUS_CHARGE_REQUESTED;
                if (!$this->authorizedUser->save()) {
                    throw new \Exception('failed to change status to charge_requested');
                }
            }

            $trans->commit();
        } catch (\Exception $e) {
            $trans->rollBack();
            Yii::error($e);
            throw new InternalServerErrorHttpException();
        }

        return 'OK';
    }

    /**
     * チャージ完了時に遷移するアクション
     * @throws UnauthorizedHttpException
     */
    public function actionPriceFinished()
    {
        if (!$this->checkAccessableActionPriceFinished()) {
            throw new UnauthorizedHttpException();
        }

        if ($this->authorizedUser->isChargeRequested()) {
            $this->redirect(['issuance/']);
        } else {
            $this->goHome();
        }
    }

    /**
     * 2. チャージ先選択
     * 20. チャージ一覧
     * @return string
     */
    public function actionList()
    {
        if ($this->authorizedUser->isActivatedUser()) {
            $chargeSources = ChargeSource::find()->joinAuthorized()->active()->all();
        } else {
            $chargeSources = ChargeSource::find()->joinAuthorized()->active()->canChargeAtFirst()->all();
        }

        return $this->render('list', [
            'chargeSources' => $chargeSources,
        ]);
    }

    /**
     * 21. チャージ先詳細
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionDetail($id = 0)
    {
        $chargeSource = ChargeSource::find()->andWhere([
            ChargeSource::tableName() . '.id' => $id,
        ])->one();

        if (!$chargeSource) {
            throw new NotFoundHttpException('サイトが見つかりません');
        }

        //認可処理用のstateを発行しsessionに保存
        //認証サイトごとにsessionキーの設定
        $key = self::POINT_SITE_AUTH_STATE_SESSION_KEY_BASE . '-' . $chargeSource->charge_source_code;
        $state = Yii::$app->session->get($key);
        if (is_null($state)) {
            $state = mt_rand();
            Yii::$app->session->set($key, (string)$state);
        }

        $this->layout = false;
        return $this->render('detail', [
            "chargeSource" => $chargeSource,
            "state"        => $state,
        ]);
    }

    /**
     * クレジットカード紹介画面
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionCreditCardDetail()
    {
        $this->layout = false;
        return $this->render('credit-card-detail', []);
    }

    /**
     * インデックスアクション時のユーザステータスからモードを取得する
     * @return bool|string モード。チャージ権限がない場合はfalse
     */
    private function checkModeActionIndex()
    {
        switch ($this->authorizedUser->registration_status) {
            case PolletUser::STATUS_NEW_USER:
                return self::PRICE_MODE_FIRST;
            case PolletUser::STATUS_CHARGE_REQUESTED:
            case PolletUser::STATUS_WAITING_ISSUE:
            case PolletUser::STATUS_ISSUED:
            case PolletUser::STATUS_ACTIVATED:
                return self::PRICE_MODE_NORMAL;
            default:
                return false;
        }
    }

    /**
     * チャージ金額選択画面にアクセス可能なユーザかどうかを判定する
     * @param $mode string モード
     * @return bool アクセス可能かどうか
     */
    private function checkAccessableActionPrice($mode)
    {
        switch ($this->authorizedUser->registration_status) {
            case PolletUser::STATUS_SITE_AUTHENTICATED:
                // 初回サイト認証完了済のユーザのみ初回チャージが可能
                return ($mode == self::PRICE_MODE_FIRST);
            case PolletUser::STATUS_CHARGE_REQUESTED:
            case PolletUser::STATUS_WAITING_ISSUE:
            case PolletUser::STATUS_ISSUED:
            case PolletUser::STATUS_ACTIVATED:
                return ($mode == self::PRICE_MODE_NORMAL);
            default:
                // サインアウト済みユーザまたは、新規ユーザはチャージ額選択はできない
                return false;
        }
    }

    /**
     * チャージ申請が可能なユーザかどうかを判定する
     * @return bool チャージ申請が可能なユーザかどうか
     */
    private function checkAccessableActionPriceRequest()
    {
        switch ($this->authorizedUser->registration_status) {
            case PolletUser::STATUS_NEW_USER:
            case PolletUser::STATUS_SIGN_OUT:
                return false;
            default:
                return true;
        }
    }

    /**
     * チャージ完了アクションにアクセス可能なユーザかどうかを判定する
     * @return bool アクセス可能かどうか
     */
    private function checkAccessableActionPriceFinished()
    {
        switch ($this->authorizedUser->registration_status) {
            case PolletUser::STATUS_CHARGE_REQUESTED:
            case PolletUser::STATUS_WAITING_ISSUE:
            case PolletUser::STATUS_ISSUED:
            case PolletUser::STATUS_ACTIVATED:
                return true;
            default:
                return false;
        }
    }
}
