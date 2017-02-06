<?php

namespace app\modules\worker\controllers;

use app\components\PushNotify;
use app\models\Information;
use app\models\PushNotificationToken;
use Yii;

/**
 * Class CronController
 * @package app\modules\worker\controllers
 */
class CronController extends CommonController
{
    /**
     * お知らせのpush通知を送信する
     *
     * @return array
     */
    public function actionNotifyInformation()
    {
        $badgeCountAndApnsTokens = PushNotificationToken::findDeviceTokens(PushNotificationToken::PLATFORM_IOS);
        $badgeCountAndGcmTokens  = PushNotificationToken::findDeviceTokens(PushNotificationToken::PLATFORM_ANDROID);

        $informations = Information::findCurrentPushNotificationTargets();
        $confirmText = PushNotify::CONFIRM_TEXT_INFO_DETAIL;

        $pushNotify = new PushNotify();
        foreach ($informations as $information) {
            $message = $information->heading;
            $uri = 'information/detail?id=' . $information->id;

            foreach ($badgeCountAndApnsTokens as $badgeCount => $apnsTokens) {
                $pushNotify->sendMultiToAPNS($apnsTokens, $message, $uri, $badgeCount, $confirmText);
            }
            foreach ($badgeCountAndGcmTokens as $gcmTokens) {
                $pushNotify->sendMultiToGCM($gcmTokens, $message, $uri, $confirmText);
            }
        }

        return [
            true,
        ];
    }

    /**
     * 共通エラーメソッド
     */
    public function actionError()
    {
        // 実際のレスポンスは config/worker.php に設定
    }
}
