<?php
namespace app\components;

use understeam\slack\Client;
use Yii;
use yii\base\Component;

/**
 * Class Slack
 *
 * @package app\modules\api\v1\components
 */
class Slack extends Component
{
    const SLACK_ICON = ':maintenance:';

    /**
     * Slackへ送信を実行する
     *
     * @param      $title
     * @param null $message
     * @param null $body
     * @return null|void
     */
    public static function send($title, $message = null, $body = null)
    {
        if (YII_ENV == YII_ENV_DEV) {
            // 開発環境では何もしない
            return;
        }

        $text = implode("\n", [
            'Environment: ' . YII_ENV,
            'URL: ' . Yii::$app->request->url,
            'Message:',
            $body,
            'POST:',
            self::dumpToText(Yii::$app->request->post()),
            'SERVER:',
            self::dumpToText($_SERVER),
        ]);

        /** @var Client $slack */
        $slack = Yii::$app->slack;

        try {
            $slack->send($title, self::SLACK_ICON, [
                [
                    'pretext' => $message,
                    'text'    => $text,
                    'color'   => '#FF0000',
                ],
            ]);
        } catch (\Exception $e) {
            // 例外は握りつぶす
            return null;
        }
    }

    /**
     * 変数等のdumpを文字列にする
     *
     * @param $var
     * @return string
     */
    public static function dumpToText($var)
    {
        ob_start();
        var_dump($var);
        $dumpResult = strip_tags(ob_get_contents());
        ob_end_clean();

        return $dumpResult;
    }
}
