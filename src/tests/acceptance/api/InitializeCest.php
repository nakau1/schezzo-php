<?php
namespace acceptance\api;

require_once __DIR__ . '/../common/PolletApiTester.php';

use PolletAcceptanceTester;

/**
 * Class InitializeCest
 * @package acceptance\api
 * ../../vendor/bin/codecept run acceptance api/InitializeCest --debug
 */
class InitializeCest
{
    const PLATFORM_IOS     = 'ios';
    const PLATFORM_ANDROID = 'android';

    /**
     * @var array
     */
    private $sendData = [];
    /**
     * @var PolletAcceptanceTester
     */
    private $tester;

    /**
     * @param PolletAcceptanceTester $tester
     */
    public function _before(PolletAcceptanceTester $tester)
    {
        $this->tester = $tester;
        // 前処理
        $this->sendData = [
            'pollet_id'    => '',
            'uuid'         => $this->tester->generateUuid(),
            'platform'     => '',
            'device_token' => '',
        ];
    }

    public function _after()
    {
        // 後処理
    }

    /**
     * 起動時APIのテスト(iOS)
     *
     * @see app\modules\api\controllers\DefaultController::actionInitialize()
     */
    public function testInitializeAppIos()
    {
        $I = $this->tester;
        $I->wantTo('アプリ起動時APIのテスト[iOS]');

        $polletId = $this->newUser(self::PLATFORM_IOS, '');
        $this->registeredUser($polletId, self::PLATFORM_IOS, '');
    }

    /**
     * 起動時APIのテスト(Android)
     *
     * @see app\modules\api\controllers\DefaultController::actionInitialize()
     */
    public function testInitializeAppAndroid()
    {
        $I = $this->tester;
        $I->wantTo('アプリ起動時APIのテスト[Android]');

        $polletId = $this->newUser(self::PLATFORM_ANDROID, '');
        $this->registeredUser($polletId, self::PLATFORM_ANDROID, '');
    }

    /**
     * 初起動のユーザー登録
     *
     * @param        $platform
     * @param string $token
     * @return string
     */
    private function newUser(string $platform, string $token = '')
    {
        $I = $this->tester;

        $I->sendPOST('/api/initialize', array_merge($this->sendData, [
            'pollet_id'    => '',
            'platform'     => $platform,
            'device_token' => $token,
        ]));
        $I->seeResponseIsJson();
        // 成功を確認
        $I->seeResponseContainsJson([
            'message' => 'OK',
        ]);

        return $I->grabDataFromResponseByJsonPath('data.pollet_id')[0];
    }

    /**
     * 既存ユーザーの起動
     * @param        $polletId
     * @param        $platform
     * @param string $token
     */
    private function registeredUser(string $polletId, string $platform, string $token = '')
    {
        $I = $this->tester;

        $I->sendPOST('/api/initialize', array_merge($this->sendData, [
            'pollet_id'    => $polletId,
            'platform'     => $platform,
            'device_token' => $token,
        ]));
        $I->seeResponseIsJson();
        // 成功を確認
        $I->seeResponseContainsJson([
            'message' => 'OK',
        ]);
        // IDが更新されていることを確認
        $I->dontSeeResponseContainsJson([
            'data' => [
                'pollet_id' => $polletId,
            ],
        ]);
    }
}