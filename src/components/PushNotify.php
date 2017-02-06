<?php
namespace app\components;

use app\models\PolletUser;
use app\models\PushNotificationToken;
use bryglen\apnsgcm\Apns;
use bryglen\apnsgcm\Gcm;
use Yii;
use yii\base\Component;

class PushNotify extends Component
{
    const CONFIRM_TEXT_TOP         = 'トップ画面を開く';
    const CONFIRM_TEXT_INQUIRY     = '問い合わせる';
    const CONFIRM_TEXT_INFO_DETAIL = '詳細を開く';

    /**
     * @var Apns
     */
    private $apns;

    /**
     * @var Gcm
     */
    private $gcm;

    /**
     * init
     */
    public function init()
    {
        parent::init();
        $this->apns = Yii::$app->apns;
        $this->gcm = Yii::$app->gcm;
    }

    /**
     * APNS(iOS)を経由して複数の端末にメッセージを送信する
     * @param string[]   $apnsTokens  iOSのデバイストークンの配列
     * @param string     $message     メッセージ
     * @param string     $uri         URI
     * @param int        $badgeCount  バッジ数
     * @param string     $confirmText アプリの確認ダイアログのボタン文言
     * @param array|null $args        通知のオプション(省略可能)
     * @return bool 成功/失敗
     */
    public function sendMultiToAPNS($apnsTokens, $message, $uri, $badgeCount, $confirmText, $args = []) : bool
    {
        // apns->sendMulti() は空の配列を扱えない
        if (empty($apnsTokens)) {
            return false;
        }

        $payloadData = [
            'uri'     => $uri,
            'confirm' => $confirmText,
        ];
        $args = !empty($args) ? $args : [
                'sound' => 'default',
                'badge' => $badgeCount,
            ];

        $result = $this->apns->sendMulti($apnsTokens, $message, $payloadData, $args);
        return !is_null($result);
    }

    /**
     * GCM(android)を経由して複数の端末にメッセージを送信する
     * @param string[]   $gcmTokens   androidのデバイストークンの配列
     * @param string     $message     メッセージ
     * @param string     $uri         URI
     * @param string     $confirmText アプリの確認ダイアログのボタン文言
     * @param array|null $args        通知のオプション(省略可能)
     * @return bool 成功/失敗
     */
    public function sendMultiToGCM($gcmTokens, $message, $uri, $confirmText, $args = []) : bool
    {
        // gcm->sendMulti() は空の配列を扱えない
        if (empty($gcmTokens)) {
            return false;
        }

        $payloadData = [
            'uri'     => $uri,
            'confirm' => $confirmText,
        ];

        $result = $this->gcm->sendMulti($gcmTokens, $message, $payloadData, $args);
        return !is_null($result);
    }

    /**
     * 指定のユーザの端末にメッセージを送信する
     * @param PolletUser $user        ユーザ
     * @param string     $message     メッセージ
     * @param string     $uri         URI
     * @param int        $badgeCount  バッジ数
     * @param string     $confirmText アプリの確認ダイアログのボタン文言
     * @param array|null $args        通知のオプション(省略可能)
     * @return bool 成功/失敗
     */
    public function sendToUser(PolletUser $user, $message, $uri, $badgeCount, $confirmText, $args = []) : bool
    {
        $apnsTokens = [];
        $gcmTokens = [];

        foreach ($user->pushNotificationTokens as $token) {
            if (!$token->is_active) {
                continue;
            }

            if ($token->platform === PushNotificationToken::PLATFORM_IOS) {
                $apnsTokens[] = $token->token;
            } elseif ($token->platform === PushNotificationToken::PLATFORM_ANDROID) {
                $gcmTokens[] = $token->token;
            }
        }

        $apnsResult = $this->sendMultiToAPNS($apnsTokens, $message, $uri, $badgeCount, $confirmText, $args);
        $gcmResult = $this->sendMultiToGCM($apnsTokens, $message, $uri, $confirmText, $args);

        return ($apnsResult && $gcmResult);
    }
}
