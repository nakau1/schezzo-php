<?php
namespace app\controllers;

use app\models\forms\SignInForm;
use app\models\PolletUser;
use Yii;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * Class AuthController
 * @package app\controllers
 */
class AuthController extends CommonController
{
    /**
     * 16. ログイン
     * @return string|Response
     */
    public function actionSignIn()
    {
        $failed = !is_null(Yii::$app->session->get(self::SESSION_FAILED_KEY));
        $formModel = new SignInForm();

        $formModel->scenario = (!$this->authorizedUser->isSignOut() || $failed) ?
            SignInForm::SCENARIO_ID_WITH_PW :
            SignInForm::SCENARIO_ID_ONLY;
        Yii::$app->session->remove(self::SESSION_FAILED_KEY);

        if ($formModel->load(Yii::$app->request->post())) {
            if ($formModel->authenticate($this->authorizedUser)) {
                $this->authorizedUser->updateStatus(PolletUser::STATUS_ACTIVATED);
                return $this->goHome();
            } else {
                Yii::$app->session->set(self::SESSION_FAILED_KEY, '1');
                $formModel->scenario = SignInForm::SCENARIO_ID_WITH_PW;
            }
        }

        return $this->render('sign-in', [
            'formModel' => $formModel,
        ]);
    }

    /**
     * ログアウト
     *
     * @param null|integer $fail
     * @return $this|Response
     * @throws NotFoundHttpException
     */
    public function actionSignOut($fail = null)
    {
        if ($this->authorizedUser->isActivatedUser()) {
            $this->authorizedUser->updateStatus(PolletUser::STATUS_SIGN_OUT);
            if ($fail) {
                // fail の引数がある場合はセディナログインエラー等で強制ログアウトさせる場合
                Yii::$app->session->set(self::SESSION_FAILED_KEY, '1');
            }
            return $this->goHome();
        } else {
            throw new NotFoundHttpException();
        }
    }
}
