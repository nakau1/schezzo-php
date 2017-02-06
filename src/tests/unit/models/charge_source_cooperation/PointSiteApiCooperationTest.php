<?php

namespace tests\unit\models\charge_source_cooperation;

use app\models\exceptions\PointSiteApiCooperation\NotCooperationException;
use app\models\exceptions\PointSiteApiCooperation\PointSiteApiNotFoundException;
use app\models\charge_source_cooperation\PointSiteApiCooperation;
use app\models\PointSiteApi;
use app\models\PointSiteToken;
use app\models\PolletUser;
use linslin\yii2\curl\Curl;
use PHPUnit_Framework_MockObject_MockObject;
use tests\unit\fixtures\PointSiteApiCooperationFixture;
use Yii;
use yii\codeception\TestCase;

class PointSiteApiCooperationTest extends TestCase
{
    public $appConfig = '@app/config/console.php';

    /** @var PHPUnit_Framework_MockObject_MockObject */
    private $curlMock;

    public function setUp()
    {
        parent::setUp();

        $this->curlMock = $this->attachCurlMock();
    }

    public function fixtures()
    {
        return [
            'fixture' => PointSiteApiCooperationFixture::class,
        ];
    }

    public function attachCurlMock()
    {
        // 実際のリクエストが発行されないようにする
        $curlMock = $this->createPartialMock(Curl::class, ['get', 'post', 'delete']);
        $curlMock->method('get')->willReturn('');
        $curlMock->method('post')->willReturn('');
        $curlMock->method('delete')->willReturn('');
        $curlMock->responseCode = 200;
        Yii::$app->set('curl', $curlMock);

        return $curlMock;
    }

    /**
     * @test
     */
    public function アクセストークンを取得する()
    {
        /** @var PolletUser $user */
        $user = PolletUser::findOne($this->fixture->pointSiteCooperatedUser);
        $chargeSource = PointSiteApiCooperationFixture::$chargeSource;

        $actualToken = PointSiteApiCooperation::getToken($chargeSource, $user->id);
        $expectedToken = PointSiteToken::find()->where([
            'pollet_user_id'     => $user->id,
            'charge_source_code' => $chargeSource,
        ])->one()->token;

        $this->assertEquals($expectedToken, $actualToken);
    }

    /**
     * @test
     */
    public function アクセストークンがなければ例外が発生する()
    {
        /** @var PolletUser $user */
        $user = PolletUser::findOne($this->fixture->polletUserId);
        $chargeSource = PointSiteApiCooperationFixture::$chargeSource;

        $this->expectException(NotCooperationException::class);
        PointSiteApiCooperation::getToken($chargeSource, $user->id);
    }

    /**
     * @test
     */
    public function 公開状態の交換APIが取得できる()
    {
        $expected = PointSiteApiCooperationFixture::$exchangeApiUrl;
        $actual = PointSiteApiCooperation::findApiUrl(
            PointSiteApiCooperationFixture::$chargeSource,
            PointSiteApi::API_NAME_EXCHANGE
        );
        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function 非公開状態の交換APIが取得できない()
    {
        $this->expectException(PointSiteApiNotFoundException::class);
        PointSiteApiCooperation::findApiUrl(
            PointSiteApiCooperationFixture::$privatePointSite,
            PointSiteApi::API_NAME_EXCHANGE
        );
    }

    /**
     * @test
     */
    public function アクセストークン取得時にポイントサイトのアクセストークン取得APIにPOSTリクエストが発行される()
    {
        $this->curlMock->response = '{"token": "token123"}';

        // postメソッドがポイント数取得APIを引数に呼ばれることをassertする
        $this->curlMock->expects($this->once())
            ->method('post')
            ->with($this->stringStartsWith('http://localhost/token'));

        // 実行
        PointSiteApiCooperation::getAccessToken('code', 'http://localhost/token');
    }

    /**
     * @test
     */
    public function 交換申請時にポイントサイトの交換APIにPOSTリクエストが発行される()
    {
        // ポイント数チェックをするので置き換えておく
        $this->curlMock->response = '{"valid_value": 5000}';

        // POSTメソッドが交換APIを引数に呼ばれることをassertする
        // ポイント数取得のバリデーションのためにポイント数取得APIが一度呼ばれる
        $this->curlMock->expects($this->at(0))
            ->method('get')
            ->willReturn('')
            ->with($this->anything());
        // 2回目に交換APIが呼ばれる
        $this->curlMock->expects($this->at(1))
            ->method('post')
            ->willReturn('')
            ->with($this->stringStartsWith(PointSiteApiCooperationFixture::$exchangeApiUrl));

        // 実行
        PointSiteApiCooperation::exchange(
            PointSiteApiCooperationFixture::$chargeSource,
            500,
            $this->fixture->pointSiteCooperatedUser,
            1
        );
    }

    /**
     * @test
     */
    public function 交換キャンセル時にポイントサイトの交換キャンセルAPIにDELETEリクエストが発行される()
    {
        // ポイント数チェックをするので置き換えておく
        $this->curlMock->response = '{"valid_value": 5000}';

        // 交換申請
        PointSiteApiCooperation::exchange(
            PointSiteApiCooperationFixture::$chargeSource,
            500,
            $this->fixture->pointSiteCooperatedUser,
            1
        );

        // deleteメソッドがポイント数取得APIを引数に呼ばれることをassertする
        $this->curlMock->expects($this->once())
            ->method('delete')
            ->with($this->stringStartsWith(PointSiteApiCooperationFixture::$cancelExchangeApiUrl));

        // 実行
        PointSiteApiCooperation::cancelExchange(PointSiteApiCooperationFixture::$chargeSource, 1);
    }

    /**
     * @test
     */
    public function ポイント数取得時にポイントサイトのポイント数取得APIにGETリクエストが発行される()
    {
        // ポイント数チェックをするので置き換えておく
        $this->curlMock->response = '{"valid_value": 5000}';

        // getメソッドがポイント数取得APIを引数に呼ばれることをassertする
        $this->curlMock->expects($this->once())
            ->method('get')
            ->with($this->stringStartsWith(PointSiteApiCooperationFixture::$pointApiUrl));

        // 実行
        PointSiteApiCooperation::fetchValidPointAsCash(
            PointSiteApiCooperationFixture::$chargeSource,
            $this->fixture->pointSiteCooperatedUser
        );
    }
}
