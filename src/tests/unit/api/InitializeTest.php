<?php

namespace tests\unit\api;

use app\models\PolletUser;
use app\modules\api\models\Initialize;
use yii\codeception\TestCase;

/**
 * Class InitializeTest
 * @package tests\unit\api
 *
 * cd /var/www/schezzo/src && ../vendor/bin/codecept run unit api/InitializeTest --debug
 */
class InitializeTest extends TestCase
{
    const PLATFORM_ANDROID = 'android';
    const PLATFORM_IOS     = 'ios';
    const DEVICE_TOKEN1    = '8fd2dfff0b0c3b92bafe1eb7a0a7c6cf1fb37dit799ad77e5f37a576d9b505df';
    const DEVICE_TOKEN2    = 'e1b474a653fa03f5af65b7edf6c092d9f28be21043fthslrdc807a254dab2b9e';

    public $appConfig = '@app/config/web.php';

    /**
     * @var Initialize
     */
    private $initialize;

    /**
     * 前処理
     */
    protected function _before()
    {
        // 実行前にする処理
        $this->initialize = new Initialize();
    }

    /**
     * ユーザーの作成
     * @see Initialize::createPolletUser()
     */
    public function testCreateUser()
    {
        $this->assertTrue($this->initialize->load([
            'uuid'         => self::generateUuid(),
            'platform'     => self::PLATFORM_ANDROID,
            'device_token' => self::DEVICE_TOKEN1,
        ]));

        $this->assertTrue($this->initialize->validate());

        $user = $this->initialize->createPolletUser();

        $this->assertNotNull($user);
        $this->assertInstanceOf(PolletUser::className(), $user);
    }

    /**
     * push通知用トークンの更新
     * @see Initialize::updateDeviceToken()
     */
    public function testUpdateToken()
    {
        // 最新のユーザーを取得
        /** @var PolletUser $user */
        $user = PolletUser::find()->orderBy([
            'id' => SORT_DESC,
        ])->one();

        $this->assertTrue($this->initialize->load([
            'pollet_id'    => $user->user_code_secret,
            'uuid'         => $user->pushNotificationTokens[0]->device_id,
            'platform'     => $user->pushNotificationTokens[0]->platform,
            'device_token' => self::DEVICE_TOKEN2,
        ]));

        $this->assertTrue($this->initialize->validate());
        $this->assertTrue($this->initialize->updateDeviceToken());
    }

    /**
     * 後処理
     */
    protected function _after()
    {
        // 終了後に実行する処理
    }

    /**
     * 簡易UUIDを作成する
     * @return string
     */
    private static function generateUuid()
    {
        return implode('-', [
            substr(uniqid(), 0, 8),
            substr(uniqid(), 0, 4),
            substr(uniqid(), 0, 4),
            substr(uniqid(), 0, 4),
            substr(uniqid(), 0, 12),
        ]);
    }
}