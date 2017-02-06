<?php
namespace app\controllers;

use app\models\charge_source_cooperation\PointSiteApiCooperation;
use app\models\exceptions\PointSiteApiCooperation\RequestFailedException;
use app\models\exceptions\PointSiteApiCooperation\ResponseBodyEmptyException;
use app\models\PointSiteApi;
use app\models\PointSiteToken;
use Yii;
use yii\web\BadRequestHttpException;
use app\models\exceptions\InternalServerErrorHttpException;
use yii\web\Response;

/**
 * Class PointSiteAuthController
 * @package app\controllers
 */
class PointSiteAuthController extends CommonController
{
    /**
     * 認可用コードの受け取りとアクセストークンの発行と保存
     *
     * 必須パラメータ
     * code … アクセストークン発行リクエスト用コード
     * state … CSRF対策用検証値
     * charge_source_code … 対象サイト特定コード
     *
     * @return string|Response
     * @throws BadRequestHttpException
     * @throws InternalServerErrorHttpException
     */
    public function actionIndex()
    {
        $params = Yii::$app->request->get();

        //エラーチェック
        if (isset($params['error'])) {
            //errorとerror_description(先方サイトでの設定値)の値をログに書き出し
            Yii::error('failed to issue the authorization code at point site. error=[' . $params['error'] . ' error_description=[' . $params['error_description'] . ']] ' . $this->getRoute());
            //稀有な異常パターンなので問い合わせ対応
            throw new BadRequestHttpException('認証エラーが発生しました。こちらからお問い合わせください。');
        }

        //パラメータチェック
        if (empty($params['code']) || empty($params['state']) || empty($params['charge_source_code'])) {
            Yii::error('parameter is not enough. param = [' . json_encode($params) . ']' . $this->getRoute());
            //稀有な異常パターンなので問い合わせ対応
            throw new BadRequestHttpException('認証エラーが発生しました。こちらからお問い合わせください。');
        }

        //sessionからstateを取り出して値の検証
        $this->inspectState($params);

        //アクセストークン発行リクエストのURLを取得
        $requestTokenUrl = PointSiteApi::findRequestTokenUrl($params['charge_source_code']);
        if (is_null($requestTokenUrl)) {
            Yii::error('the request token URL is not found. charge_source_code = [' . $params['charge_source_code'] . ']' . $this->getRoute());
            //設定ミス。稀有な異常パターンなのでTOPへ戻す。異常はログからアラートで検知する。
            $this->goHome();
            return;
        }

        //アクセストークン発行リクエスト
        try {
            $accessToken = PointSiteApiCooperation::getAccessToken($params['code'], $requestTokenUrl,
                $this->getRoute());
        } catch (RequestFailedException $e) {
            //稀有な異常パターンなので問い合わせ対応
            throw new BadRequestHttpException('認証エラーが発生しました。こちらからお問い合わせください。');
        } catch (ResponseBodyEmptyException $e) {
            //タイムアウトか先方で出力異常。稀有な異常パターンだがとりあえずやり直してみてもらいたい。
            throw new BadRequestHttpException('認証に失敗しました。恐れ入りますがもう一度はじめからやり直してください。');
        }

        //$token保存
        $isSaveToken = PointSiteToken::add($this->authorizedUser->id, $accessToken, $params['charge_source_code']);

        if (!$isSaveToken) {
            throw new InternalServerErrorHttpException();
        }

        //チャージ画面にリダイレクト
        $this->redirect(['/charge/index', 'charge_source_code' => $params['charge_source_code']]);
    }

    /**
     * sessionに保存した値と認可用code受け取りのstate値の検証
     *
     * @param array $params
     * @throws BadRequestHttpException
     */
    private function inspectState(array $params)
    {
        $key = ChargeController::POINT_SITE_AUTH_STATE_SESSION_KEY_BASE . '-' . $params['charge_source_code'];
        $sessionState = Yii::$app->session->get($key);
        if ($sessionState !== $params['state']) {
            Yii::error('the inspection of agree state is failed. session key=[' . $key . '] session state=[ ' . $sessionState . '] state=[' . $params['state'] . $this->getRoute());
            throw new BadRequestHttpException('認証に失敗しました。恐れ入りますがもう一度はじめからやり直してください。');
        }
    }
}
