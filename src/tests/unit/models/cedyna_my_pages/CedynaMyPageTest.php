<?php

namespace tests\unit\models\cedyna_my_pages;

use app\components\CedynaMyPage;
use app\models\PolletUser;
use app\models\TradingHistory;
use tests\unit\fixtures\CedynaMyPageFixture;
use yii\codeception\TestCase;

class CedynaMyPageTest extends TestCase
{
    public $appConfig = '@app/config/console.php';

    public function setUp()
    {
        parent::setUp();
    }

    public function fixtures()
    {
        return [
            CedynaMyPageFixture::class,
        ];
    }

    /**
     * @test
     */
    public function カード残高が数値として取得できる()
    {
        /** @var PolletUser $user */
        $user = PolletUser::findOne(CedynaMyPageFixture::$polletUserId);
        $myPage = CedynaMyPage::getInstance();
        $myPage->login($user->cedyna_id, $user->rawPassword);
        $value = $myPage->cardValue();

        $this->assertTrue(is_int($value));
    }

    /**
     * @test
     */
    public function 利用履歴がオブジェクトの配列として取得できる()
    {
        /** @var PolletUser $user */
        $user = PolletUser::findOne(CedynaMyPageFixture::$polletUserId);
        $myPage = CedynaMyPage::getInstance();
        $myPage->login($user->cedyna_id, $user->rawPassword);
        $histories = $myPage->tradingHistories('1603');

        $this->assertTrue(is_array($histories));
        foreach ($histories as $history) {
            $this->assertInstanceOf(TradingHistory::class, $history);
        }
    }

    /**
     * @test
     */
    public function メールアドレスの送信結果を取得できる()
    {
        $myPage = CedynaMyPage::getInstance();
        $polletId = '11111111';
        // 送信できたらtrue
        $this->assertTrue($myPage->sendIssuingFormLink('point-wallet-system@oz-vision.co.jp', $polletId));
        // 送信できなかったらfalse
        $this->assertFalse($myPage->sendIssuingFormLink('', $polletId));
    }

    /**
     * @test
     */
    public function メールアドレス送信のURLに提携先idと経路先idをつける()
    {
        $myPage = CedynaMyPage::getInstance();
        $url = $myPage->urls;
        $polletId = '11111111';
        $assertUrl = $url['sendIssuingFormLink'] . '?partner_id=' . $polletId . '&route_id=0001';
        $this->assertEquals($assertUrl, $myPage->getIssuingFormLinkWithParam($polletId));
    }

    public function tearDown()
    {
        parent::tearDown();
    }
}