<?php
namespace app\controllers;

use app\models\exceptions\InternalServerErrorHttpException;
use app\models\exceptions\UnauthorizedHttpException;
use app\models\forms\IssuanceForm;
use app\models\PolletUser;
use Yii;

/**
 * カード発行手続き
 * Class IssuanceController
 * @package app\controllers
 */
class IssuanceController extends CommonController
{
    /**
     * 6. メールアドレス入力
     * 7. カード発行手続き完了画面
     * @return string
     * @throws UnauthorizedHttpException
     * @throws InternalServerErrorHttpException
     */
    public function actionIndex()
    {
        if (!$this->checkAccess()) {
            throw new UnauthorizedHttpException();
        }

        $formModel = new IssuanceForm();

        if ($formModel->load(Yii::$app->request->post()) && $formModel->validate()) {
            // 認証メール送信
            if ($formModel->send($this->authorizedUser->id)) {
                $trans = Yii::$app->db->beginTransaction();
                try {
                    $this->authorizedUser->registration_status = PolletUser::STATUS_WAITING_ISSUE;
                    $this->authorizedUser->mail_address = $formModel->mail_address;
                    if (!$this->authorizedUser->save()) {
                        throw new \Exception('failed change to waiting-issue.');
                    }
                    $trans->commit();
                } catch (\Exception $e) {
                    $trans->rollBack();
                    throw new InternalServerErrorHttpException();
                }
                // 7. カード発行手続き完了画面を表示
                return $this->render('reception');
            } else {
                $formModel->addInvalidEmailError();
            }
        }

        return $this->render('index', [
            "formModel" => $formModel,
        ]);
    }

    /**
     * カード発行手続きが可能なユーザかどうかを判定する
     * @return bool
     */
    private function checkAccess()
    {
        return ($this->authorizedUser->isChargeRequested() || $this->authorizedUser->isWaitingIssue());
    }
}
