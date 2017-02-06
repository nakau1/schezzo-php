<?php
namespace tests\acceptance_exchange\api;
use app\models\ChargeSource;
use app\models\Reception;
use AcceptanceTester;

/**
 * Class ReceptionCest
 * @package tests\acceptance_exchange\api
 * vendor/bin/codecept run acceptance_exchange api/ReceptionCest --debug
 */
class ReceptionCest extends BaseCest
{
    /**
     * 受付APIの必須パラメータに関するテスト
     * @see app\modules\exchange\controllers\DefaultController::actionReception()
     */
    public function testRequired()
    {
        $I = $this->tester;
        $I->wantToTest("受付APIの必須パラメータに関するテスト");

        // -------------------------------------------------------------------

        $I->amGoingTo('正常にすべての必須パラメータを渡す');
        $I->expect('OKを返す');
        $I->sendPOST($this->baseURL(), array_merge($this->baseParams(), [
            'pollet_id' => self::ACTIVATED_USER_ID,
        ]));
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'message' => 'OK',
        ]);

        // -------------------------------------------------------------------

        $I->amGoingTo('交換サイトコードをURLにつけない');
        $I->expect('「必須項目が存在しない」のエラーを返す');
        $I->sendPOST('/reception/', array_merge($this->baseParams(), [
            'pollet_id' => self::ACTIVATED_USER_ID,
        ]));
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'message' => 'Invalid Parameters',
            'data' => ['required parameter is not set']
        ]);

        // -------------------------------------------------------------------

        $I->amGoingTo('APIキーを渡さない');
        $I->expect('「必須項目が存在しない」のエラーを返す');
        $params = $this->baseParams();
        unset($params['api_key']);
        $I->sendPOST($this->baseURL(), array_merge($params, [
            'pollet_id' => self::ACTIVATED_USER_ID,
        ]));
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'message' => 'Invalid Parameters',
            'data' => ['required parameter is not set']
        ]);

        // -------------------------------------------------------------------

        $I->amGoingTo('金額を渡さない');
        $I->expect('「必須項目が存在しない」のエラーを返す');
        $params = $this->baseParams();
        unset($params['amount']);
        $I->sendPOST($this->baseURL(), array_merge($params, [
            'pollet_id' => self::ACTIVATED_USER_ID,
        ]));
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'message' => 'Invalid Parameters',
            'data' => ['required parameter is not set']
        ]);

        // -------------------------------------------------------------------
        // 非必須項目
        // -------------------------------------------------------------------

        $I->amGoingTo('有効期限遅延フラグを渡さない');
        $I->comment('有効期限遅延フラグは必須ではない');
        $I->expect('OKを返す');
        $params = $this->baseParams();
        unset($params['delay']);
        $I->sendPOST($this->baseURL(), array_merge($params, [
            'pollet_id' => self::ACTIVATED_USER_ID,
        ]));
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'message' => 'OK',
        ]);
    }

    /**
     * 受付APIの「PolletID」と「カード番号」のパラメータに関するテスト
     * @see app\modules\exchange\controllers\DefaultController::actionReception()
     */
    public function testParametersCardNumberAndPolletID()
    {
        $I = $this->tester;
        $I->wantToTest("受付APIの「PolletID」と「カード番号」のパラメータに関するテスト");

        // -------------------------------------------------------------------
        // 正常系
        // -------------------------------------------------------------------

        $I->amGoingTo('PolletIDのみを渡す');
        $I->expect('OKを返す');
        $I->sendPOST($this->baseURL(), array_merge($this->baseParams(), [
            'pollet_id' => self::ACTIVATED_USER_ID,
        ]));
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'message' => 'OK',
        ]);

        // -------------------------------------------------------------------

        $I->amGoingTo('カード番号のみを渡す');
        $I->expect('OKを返す');
        $I->sendPOST($this->baseURL(), array_merge($this->baseParams(), [
            'card_number' => self::ACTIVATED_USER_CARD_NO,
        ]));
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'message' => 'OK',
        ]);

        // -------------------------------------------------------------------

        $I->amGoingTo('PolletIDとカード番号両方を渡す');
        $I->expect('OKを返す');
        $I->sendPOST($this->baseURL(), array_merge($this->baseParams(), [
            'pollet_id' => self::NEW_USER_ID,
            'card_number' => self::ACTIVATED_USER_CARD_NO,
        ]));
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'message' => 'OK',
        ]);

        // -------------------------------------------------------------------
        // 異常系
        // -------------------------------------------------------------------

        $I->amGoingTo('PolletIDとカード番号両方とも空で渡す');
        $I->expect('エラーを返す');
        $I->sendPOST($this->baseURL(), array_merge($this->baseParams(), [
            // No Set
        ]));
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'message' => 'Invalid Parameters',
            'data' => ['required parameter is not set']
        ]);

        // -------------------------------------------------------------------

        $I->amGoingTo('存在しないPolletIDを渡す');
        $I->expect('「ユーザが存在しない」のエラーを返す');
        $I->sendPOST($this->baseURL(), array_merge($this->baseParams(), [
            'pollet_id' => self::NO_EXISTS_USER_ID,
        ]));
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'message' => 'Failed',
            'data' => ['specified user does not exist']
        ]);

        // -------------------------------------------------------------------

        $I->amGoingTo('存在しないカード番号を渡す');
        $I->expect('「ユーザが存在しない」のエラーを返す');
        $I->sendPOST($this->baseURL(), array_merge($this->baseParams(), [
            'card_number' => self::NO_EXISTS_CARD_NO,
        ]));
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'message' => 'Failed',
            'data' => ['specified user does not exist']
        ]);

        // -------------------------------------------------------------------

        $I->amGoingTo('存在しないPolletIDと存在するカード番号を渡す');
        $I->expect('OKを返す');
        $I->sendPOST($this->baseURL(), array_merge($this->baseParams(), [
            'pollet_id' => self::NO_EXISTS_USER_ID,
            'card_number' => self::ACTIVATED_USER_CARD_NO,
        ]));
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'message' => 'OK',
        ]);

        // -------------------------------------------------------------------

        $I->amGoingTo('存在するPolletIDと存在しないカード番号を渡す');
        $I->expect('「ユーザが存在しない」のエラーを返す');
        $I->sendPOST($this->baseURL(), array_merge($this->baseParams(), [
            'pollet_id' => self::ACTIVATED_USER_ID,
            'card_number' => self::NO_EXISTS_CARD_NO,
        ]));
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'message' => 'Failed',
            'data' => ['specified user does not exist']
        ]);

        // -------------------------------------------------------------------

        $I->amGoingTo('存在しないPolletIDと存在しないカード番号を渡す');
        $I->expect('「ユーザが存在しない」のエラーを返す');
        $I->sendPOST($this->baseURL(), array_merge($this->baseParams(), [
            'pollet_id' => self::NO_EXISTS_USER_ID,
            'card_number' => self::NO_EXISTS_CARD_NO,
        ]));
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'message' => 'Failed',
            'data' => ['specified user does not exist']
        ]);

        // -------------------------------------------------------------------

        $I->amGoingTo('カード番号を渡してPolletIDを渡さない');
        $I->comment('片方があればエラーにはならない');
        $I->expect('OKを返す');
        $params = $this->baseParams();
        unset($params['pollet_id']);
        $I->sendPOST($this->baseURL(), array_merge($params, [
            'card_number' => self::ACTIVATED_USER_CARD_NO,
        ]));
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'message' => 'OK',
        ]);

        // -------------------------------------------------------------------

        $I->amGoingTo('カード番号を渡さずにPolletIDを渡す');
        $I->comment('片方があればエラーにはならない');
        $I->expect('OKを返す');
        $params = $this->baseParams();
        unset($params['card_number']);
        $I->sendPOST($this->baseURL(), array_merge($params, [
            'pollet_id' => self::ACTIVATED_USER_ID,
        ]));
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'message' => 'OK',
        ]);


        // -------------------------------------------------------------------

        $I->amGoingTo('カード番号もPolletIDを渡さない');
        $I->comment('片方があればエラーにはならない');
        $I->expect('「必須項目が存在しない」のエラーを返す');
        $params = $this->baseParams();
        unset($params['pollet_id']);
        unset($params['card_number']);
        $I->sendPOST($this->baseURL(), array_merge($params, [
            // NO Set
        ]));
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'message' => 'Invalid Parameters',
            'data' => ['required parameter is not set']
        ]);
    }

    /**
     * 金額に関するテスト
     * @see app\modules\exchange\controllers\DefaultController::actionReception()
     */
    public function testAmount()
    {
        $I = $this->tester;
        $I->wantToTest("金額に関するテスト(主に限界値テスト)");

        $chargeSource = ChargeSource::find()->andWhere([
            'charge_source_code' => self::SITE_CODE,
        ])->one();

        $min = $chargeSource->min_value;
        $max = Reception::MAX_PRICE_PER_CHARGE;

        // -------------------------------------------------------------------

        $I->amGoingTo('最低金額と同額の金額を渡す');
        $I->expect('OKを返す');
        $I->sendPOST($this->baseURL(), array_merge($this->baseParams(), [
            'pollet_id' => self::ACTIVATED_USER_ID,
            'amount' => $min,
        ]));
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'message' => 'OK',
        ]);

        // -------------------------------------------------------------------

        $I->amGoingTo('最低金額より大きい金額を渡す');
        $I->expect('OKを返す');
        $I->sendPOST($this->baseURL(), array_merge($this->baseParams(), [
            'pollet_id' => self::ACTIVATED_USER_ID,
            'amount' => $min + 1,
        ]));
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'message' => 'OK',
        ]);

        // -------------------------------------------------------------------

        $I->amGoingTo('最低金額より小さい金額を渡す');
        $I->expect('「amount が範囲外の数値である」エラーを返す');
        $I->sendPOST($this->baseURL(), array_merge($this->baseParams(), [
            'pollet_id' => self::ACTIVATED_USER_ID,
            'amount' => $min - 1,
        ]));
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'message' => 'Invalid Parameters',
            'data' => ['amount is out of the range'],
        ]);

        // -------------------------------------------------------------------

        $I->amGoingTo('最大金額と同額の金額を渡す');
        $I->expect('OKを返す');
        $I->sendPOST($this->baseURL(), array_merge($this->baseParams(), [
            'pollet_id' => self::ACTIVATED_USER_ID,
            'amount' => $max,
        ]));
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'message' => 'OK',
        ]);

        // -------------------------------------------------------------------

        $I->amGoingTo('最大金額より大きい金額を渡す');
        $I->expect('「amount が範囲外の数値である」エラーを返す');
        $I->sendPOST($this->baseURL(), array_merge($this->baseParams(), [
            'pollet_id' => self::ACTIVATED_USER_ID,
            'amount' => $max + 1,
        ]));
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'message' => 'Invalid Parameters',
            'data' => ['amount is out of the range'],
        ]);

        // -------------------------------------------------------------------

        $I->amGoingTo('最大金額より小さい金額を渡す');
        $I->expect('OKを返す');
        $I->sendPOST($this->baseURL(), array_merge($this->baseParams(), [
            'pollet_id' => self::ACTIVATED_USER_ID,
            'amount' => $max - 1,
        ]));
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'message' => 'OK',
        ]);
    }

    /**
     * 受付APIのサイト認証のテスト
     * @see app\modules\exchange\controllers\DefaultController::actionReception()
     */
    public function testSiteAuth()
    {
        $I = $this->tester;
        $I->wantToTest("受付APIのサイト認証のテスト");

        // -------------------------------------------------------------------

        $I->amGoingTo('存在しないサイトコードを渡す');
        $I->expect('「渡されたた「交換サイトコード」 と、「APIキー」では交換サイトを認証できなかった」エラーを返す');
        $I->sendPOST('/reception/unknown', array_merge($this->baseParams(), [
            'pollet_id' => self::ACTIVATED_USER_ID,
        ]));
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'message' => 'Unauthorized',
        ]);

        // -------------------------------------------------------------------

        $I->amGoingTo('存在しないAPIキーを渡す');
        $I->expect('「渡されたた「交換サイトコード」 と、「APIキー」では交換サイトを認証できなかった」エラーを返す');
        $I->sendPOST($this->baseURL(), array_merge($this->baseParams(), [
            'api_key' => 'unknown',
            'pollet_id' => self::ACTIVATED_USER_ID,
        ]));
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'message' => 'Unauthorized',
        ]);
    }

    /**
     * @inheritdoc
     */
    protected function baseParams()
    {
        return [
            'api_key'     => self::API_KEY,
            'card_number' => '',
            'pollet_id'   => '',
            'amount'      => 1000,
            'delay'       => 1,
        ];
    }

    /**
     * @inheritdoc
     */
    protected function baseURL()
    {
        return '/reception/'. self::SITE_CODE;
    }

    /**
     * @inheritdoc
     */
    public function _before(AcceptanceTester $tester)
    {
        $this->tester = $tester;
    }
}