<?php
namespace tests\acceptance_exchange\api;

/**
 * Class InquireCest
 * @package tests\acceptance_exchange\api
 * vendor/bin/codecept run acceptance_exchange api/InquireCest --debug
 */
class InquireCest extends BaseCest
{
    /**
     * 状態確認APIの正常系テスト
     * @see app\modules\exchange\controllers\DefaultController::actionInquire()
     */
    public function testInquire()
    {
        $I = $this->tester;
        $I->wantToTest("状態確認APIの正常系テスト");

        // -------------------------------------------------------------------

        $I->amGoingTo('正常にすべての必須パラメータを渡す');
        $I->expect('OKを返す');
        $I->sendPOST($this->baseURL(), array_merge($this->baseParams(), [
            'reception_ids' => implode(',', $this->receptionIds),
        ]));
        $I->seeResponseIsJson();

        $data = [];
        foreach ($this->receptionIds as $receptionId) {
            $data[] = [
                'reception_id' => $receptionId,
                'amount' => 1000,
                'reception_status' => 'accepted',
            ];
        }
        $I->seeResponseContainsJson([
            'message' => 'OK',
            'data' => $data,
        ]);

        // -------------------------------------------------------------------

        $added = array_merge($this->receptionIds, ['hogehoge']);
        $data[] = [
            'reception_id' => 'hogehoge',
            'amount' => null,
            'reception_status' => 'unknown',
        ];

        $I->amGoingTo('存在しない受付IDを追加で渡す');
        $I->expect('OKを返す。存在しない受付IDのステータスはunknownで返される');
        $I->sendPOST($this->baseURL(), array_merge($this->baseParams(), [
            'reception_ids' => implode(',', $added),
        ]));
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'message' => 'OK',
            'data' => $data,
        ]);
    }

    /**
     * 状態確認APIの必須パラメータに関するテスト
     * @see app\modules\exchange\controllers\DefaultController::actionInquire()
     */
    public function testRequired()
    {
        $I = $this->tester;
        $I->wantToTest("状態確認APIの必須パラメータに関するテスト");

        // -------------------------------------------------------------------

        $I->amGoingTo('交換サイトコードをURLにつけない');
        $I->expect('「必須項目が存在しない」のエラーを返す');
        $I->sendPOST('/inquire/', array_merge($this->baseParams(), [
            'reception_ids' => implode(',', $this->receptionIds),
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
            'reception_ids' => implode(',', $this->receptionIds),
        ]));
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'message' => 'Invalid Parameters',
            'data' => ['required parameter is not set']
        ]);

        // -------------------------------------------------------------------

        $I->amGoingTo('受付IDを渡さない');
        $I->comment('reception_idsがない場合は空文字を渡したものとして扱われる');
        $I->expect('OKを返す');
        $params = $this->baseParams();
        unset($params['reception_ids']);
        $I->sendPOST($this->baseURL(), array_merge($params, [
            // Not Set
        ]));
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'message' => 'OK',
            'data' => [],
        ]);

        // -------------------------------------------------------------------

        $I->amGoingTo('受付IDに空文字を渡す');
        $I->expect('OKを返す(データは空で返る)');
        $params = $this->baseParams();
        unset($params['reception_ids']);
        $I->sendPOST($this->baseURL(), array_merge($params, [
            'reception_ids' => '',
        ]));
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'message' => 'OK',
            'data' => [],
        ]);
    }

    /**
     * 状態確認API受付ID数の限界値テスト
     * @see app\modules\exchange\controllers\DefaultController::actionInquire()
     */
    public function testOverIds()
    {
        $I = $this->tester;
        $I->wantToTest("状態確認APIの受付ID数の限界値テスト");

        // -------------------------------------------------------------------

        $I->amGoingTo('99件の受付IDを返す');
        $I->expect('OKを返す');
        $I->sendPOST($this->baseURL(), array_merge($this->baseParams(), [
            'reception_ids' => $this->generateCommadSeparatedReceptionIds(99),
        ]));
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'message' => 'OK',
        ]);

        // -------------------------------------------------------------------

        $I->amGoingTo('100件の受付IDを返す');
        $I->expect('OKを返す');
        $I->sendPOST($this->baseURL(), array_merge($this->baseParams(), [
            'reception_ids' => $this->generateCommadSeparatedReceptionIds(100),
        ]));
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'message' => 'OK',
        ]);

        // -------------------------------------------------------------------

        $I->amGoingTo('101件の受付IDを返す');
        $I->expect('「受付IDが多すぎる」エラーを返す');
        $I->sendPOST($this->baseURL(), array_merge($this->baseParams(), [
            'reception_ids' => $this->generateCommadSeparatedReceptionIds(101),
        ]));
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'message' => 'Invalid Parameters',
            'data' => ['reception identifiers are too many'],
        ]);
    }

    /**
     * 状態確認APIのサイト認証のテスト
     * @see app\modules\exchange\controllers\DefaultController::actionInquire()
     */
    public function testSiteAuth()
    {
        $I = $this->tester;
        $I->wantToTest("状態確認APIのサイト認証のテスト");

        // -------------------------------------------------------------------

        $I->amGoingTo('存在しないサイトコードを渡す');
        $I->expect('「渡されたた「交換サイトコード」 と、「APIキー」では交換サイトを認証できなかった」エラーを返す');
        $I->sendPOST('/inquire/unknown', array_merge($this->baseParams(), [
            'reception_ids' => implode(',', $this->receptionIds),
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
            'reception_ids' => implode(',', $this->receptionIds),
        ]));
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'message' => 'Unauthorized',
        ]);
    }

    /**
     * 状態確認APIの別サイトを使用しての認証のテスト
     * @see app\modules\exchange\controllers\DefaultController::actionInquire()
     */
    public function testAnotherSiteAuth()
    {
        $I = $this->tester;
        $I->wantToTest("状態確認APIの別サイトを使用しての認証のテスト");

        // -------------------------------------------------------------------

        $I->amGoingTo('別のサイトの認証情報を渡す');
        $I->expect('OKを返す。すべてunknown扱いとなる');
        $I->sendPOST('/inquire/'. self::ANOTHER_SITE_CODE, array_merge($this->baseParams(), [
            'api_key' => self::ANOTHER_API_KEY,
            'reception_ids' => implode(',', $this->receptionIds),
        ]));
        $I->seeResponseIsJson();

        $data = [];
        foreach ($this->receptionIds as $receptionId) {
            $data[] = [
                'reception_id' => $receptionId,
                'amount' => null,
                'reception_status' => 'unknown',
            ];
        }
        $I->seeResponseContainsJson([
            'message' => 'OK',
            'data' => $data,
        ]);
    }

    /**
     * @inheritdoc
     */
    protected function baseURL()
    {
        return '/inquire/'. self::SITE_CODE;
    }
}