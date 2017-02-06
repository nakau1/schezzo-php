<?php
namespace app\helpers;
use app\controllers\ChargeController;
use app\models\forms\SignInForm;
use app\models\ChargeSource;
use app\models\PolletUser;
use yii\web\NotFoundHttpException;

/**
 * Class Dispatcher
 * ユーザのステータスによって処理を切り分けるクラス
 * @package app\helpers
 */
class Dispatcher
{
    /**
     * @param PolletUser $user
     * @return array
     * @throws NotFoundHttpException
     */
    public static function forIndex($user)
    {
        if (is_null($user)) {
            throw new NotFoundHttpException();
        }

        switch ($user->registration_status) {
            case PolletUser::STATUS_NEW_USER:
                return ['start/'];
            case PolletUser::STATUS_SITE_AUTHENTICATED:
                return self::actionSiteAuthenticated();
            case PolletUser::STATUS_CHARGE_REQUESTED:
            case PolletUser::STATUS_WAITING_ISSUE:
            case PolletUser::STATUS_ISSUED:
                return ['top/'];
            case PolletUser::STATUS_ACTIVATED:
                $signInForm = new SignInForm();
                $signInForm->scenario = SignInForm::SCENARIO_AUTO;
                if ($signInForm->authenticate($user)) {
                    return ['top/'];
                } else {
                    return ['auth/sign-in'];
                }
            case PolletUser::STATUS_SIGN_OUT:
                return ['auth/sign-in'];
            default:
                throw new NotFoundHttpException();
        }
    }

    /**
     * @param PolletUser $user
     * @return array|bool
     */
    public static function forDefaultBackAction($user)
    {
        if (is_null($user)) {
            return false;
        }

        switch ($user->registration_status) {
            case PolletUser::STATUS_NEW_USER:
                return ['start/'];
            case PolletUser::STATUS_SIGN_OUT:
                return ['auth/sign-in'];
            case PolletUser::STATUS_SITE_AUTHENTICATED:
                return ['charge/list'];
            case PolletUser::STATUS_CHARGE_REQUESTED:
            case PolletUser::STATUS_WAITING_ISSUE:
            case PolletUser::STATUS_ISSUED:
            case PolletUser::STATUS_ACTIVATED:
                return ['top/'];
            default:
                return false;
        }
    }

    /**
     * @return array
     */
    private static function actionSiteAuthenticated()
    {
        $chargeSource = ChargeSource::find()->joinAuthorized(true)->active()->one();
        return ['charge/price',
            'code' => $chargeSource->charge_source_code,
            'mode' => ChargeController::PRICE_MODE_FIRST,
        ];
    }
}
