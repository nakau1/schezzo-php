<?php
namespace tests\unit\api;

date_default_timezone_set('Asia/Tokyo');

use app\helpers\Date;
use app\models\ChargeRequestHistory;
use app\models\PolletUser;
use app\modules\exchange\models\ApplyInquire;
use app\modules\exchange\models\Consistency;
use app\modules\exchange\models\Reception;
use app\modules\exchange\models\SiteAuthorize;
use yii\codeception\TestCase;

/**
 * Class ExchangeApiTest
 * @package tests\unit\api
 *
 * cd /var/www/schezzo/src
 * ../vendor/bin/codecept run unit api/ExchangeApiTest --debug
 */
class ExchangeApiTest extends TestCase
{
    public $appConfig = '@app/tests/unit/api/config.php';

    /**
     * 交換サイトコード
     */
    const SITE_CODE = 'demodemo';

    /**
     * APIキー
     */
    const API_KEY = 'demodemodemodemo';

    /**
     * 新規ユーザのPolletユーザID
     */
    const NEW_USER_ID = 100000;

    /**
     * アクティベートされたユーザのPolletユーザID
     */
    const ACTIVATED_USER_ID = 100100;

    /**
     * アクティベートされたユーザのカード番号
     */
    const ACTIVATED_USER_CARD_NO = '2274020918206';

    const APPLIED_RECEPTION       = 'status of Reception after apply';
    const APPLIED_CHARGE_REQ      = 'status of ChargeRequestHistory after apply';
    const CONSISTENCED_RECEPTION  = 'status of Reception after consistency';
    const CONSISTENCED_CHARGE_REQ = 'status of ChargeRequestHistory after consistency';
    const USER_STATUS             = 'status of Pollet finally';

    /**
     * @var SiteAuthorize
     */
    private $siteAuth;

    /**
     * @var Reception
     */
    private $reception;

    /**
     * @var ApplyInquire
     */
    private $inquire;

    /**
     * @var Consistency
     */
    private $consistency;

    /**
     * 前処理
     */
    protected function _before()
    {
        $this->reset();
    }

    private function reset()
    {
        $this->siteAuth = new SiteAuthorize();
        $this->reception = new Reception();
        $this->inquire = new ApplyInquire();
        $this->consistency = new Consistency();
    }

    /**
     * サイト認証モデルの動作テスト
     * - SiteAuthorizeモデルを使って正常にサイト認証ができるかどうか
     */
    public function testSiteAuthorize()
    {
        $receptionParams = $this->generateReceptionParameters([
            'pollet_user_id'=> self::NEW_USER_ID,
        ]);

        // サイト認証
        $this->assertTrue($this->siteAuth->load($receptionParams));
        $this->assertTrue($this->siteAuth->authorize());
        $this->assertSame(self::SITE_CODE, $this->siteAuth->chargeSource->charge_source_code);
    }

    /**
     * 受付モデルの動作テスト
     * - 受付を正常に行えるかどうか
     */
    public function testReception()
    {
        $receptionParams = $this->generateReceptionParameters([
            'pollet_user_id'=> self::NEW_USER_ID,
        ]);
        $this->siteAuth->load($receptionParams);
        $this->siteAuth->authorize();
        $receptionParams['charge_source_code'] = $this->siteAuth->getChargeSource()->charge_source_code;

        // 受付
        $this->reception->setScenario(Reception::SCENARIO_API_REQUEST);
        $this->reception->siteAuthorize = $this->siteAuth;
        $this->assertTrue($this->reception->load($receptionParams));
        $this->assertTrue($this->reception->accept());
    }

    /**
     * 受付実行後のリレーション確認テスト
     * - 受付を実行したら、関連レコードと追加とリレーションが正常にできるかどうか
     */
    public function testReceptionRelation()
    {
        $receptionId = $this->executeReception([
            'pollet_user_id'=> self::NEW_USER_ID,
        ]);

        $reception = $this->findByReceptionId($receptionId);
        $this->assertNotNull($reception);
        $chargeRequest = $reception->chargeRequestHistory;
        $this->assertNotNull($chargeRequest);
        $user = $reception->polletUser;
        $this->assertNotNull($user);
    }

    /**
     * 受付モデルの有効期限フラグ動作テスト
     * - 有効期限遅延フラグを1で渡すと、有効期限が10日後になるか
     * - 有効期限遅延フラグを0で渡すと、有効期限が5分後になるか
     */
    public function testReceptionExpireDate()
    {
        // 有効期限遅延フラグを1で渡すと、有効期限が10日後になるか
        $after10days = Date::now()->addDays(10)->format(Date::DATETIME_FORMAT);
        $receptionId1 = $this->executeReception([
            'pollet_user_id' => self::NEW_USER_ID,
            'delay' => 1,
        ]);
        $reception1 = $this->findByReceptionId($receptionId1);
        $this->reset(); //リセットしないとデータが保たれない

        // 有効期限遅延フラグを0で渡すと、有効期限が5分後になるか
        $after5min = Date::now()->addMinutes(5)->format(Date::DATETIME_FORMAT);
        $receptionId0 = $this->executeReception([
            'pollet_user_id' => self::NEW_USER_ID,
            'delay' => 0,
        ]);
        $reception0 = $this->findByReceptionId($receptionId0);

        // 確認
        $this->assertSame($after10days, $reception1->expiry_date);
        $this->assertSame($after5min,   $reception0->expiry_date);
    }

    /**
     * 受付モデルの有効期限動作テスト
     * - 有効期限遅延フラグによって指定の日付で期限が切れた動作が行われるか
     */
    public function testReceptionExpireDateResult()
    {
        $receptionId0 = $this->executeReception([
            'pollet_user_id' => self::NEW_USER_ID,
            'delay' => 0,
        ]);
        $this->reset(); //リセットしないとデータが保たれない

        $receptionId1 = $this->executeReception([
            'pollet_user_id' => self::NEW_USER_ID,
            'delay' => 1,
        ]);

        // 現在
        Date::setTestNow(Date::now());
        $res = $this->executeInquire([$receptionId0, $receptionId1]);

        $this->assertSame(Reception::RECEPTION_STATUS_ACCEPTED, $res[0]['reception_status']);
        $this->assertSame(Reception::RECEPTION_STATUS_ACCEPTED, $res[1]['reception_status']);

        // 5分後
        Date::setTestNow(Date::now()->addMinutes(5)->addSecond(3)); // 3秒間余裕をもたせる
        $res = $this->executeInquire([$receptionId0, $receptionId1]);

        $this->assertSame(Reception::RECEPTION_STATUS_EXPIRED, $res[0]['reception_status']);
        $this->assertSame(Reception::RECEPTION_STATUS_ACCEPTED, $res[1]['reception_status']);

        // 10日後
        Date::setTestNow(Date::now()->addDays(10)->addSecond(3)); // 3秒間余裕をもたせる
        $res = $this->executeInquire([$receptionId0, $receptionId1]);

        $this->assertSame(Reception::RECEPTION_STATUS_EXPIRED, $res[0]['reception_status']);
        $this->assertSame(Reception::RECEPTION_STATUS_EXPIRED, $res[1]['reception_status']);

        // 念のため元に戻しておく
        Date::setTestNow(Date::now());
    }

    /**
     * 新規ユーザ受付実行後のステータス確認テスト
     * - 受付を実行したら、仕様通りのステータスになっているかどうか
     */
    public function testReceptionStatusesForNewUser()
    {
        $receptionId = $this->executeReception([
            'pollet_user_id'=> self::NEW_USER_ID,
        ]);

        // 受付後のステータスチェック
        // -------------------------------------------------------------
        // (期待値)
        // Reception            => accepted
        // ChargeRequestHistory => accepted_reception
        // -------------------------------------------------------------
        $reception = $this->findByReceptionId($receptionId);
        $this->assertSame(Reception::RECEPTION_STATUS_ACCEPTED, $reception->reception_status);
        $this->assertSame(ChargeRequestHistory::STATUS_ACCEPTED_RECEPTION, $reception->chargeRequestHistory->processing_status);
    }

    /**
     * アクティベート済ユーザ受付実行後のステータス確認テスト
     * - 受付を実行したら、仕様通りのステータスになっているかどうか
     */
    public function testReceptionStatusesForActivateUser()
    {
        $receptionId = $this->executeReception([
            'pollet_user_id'=> self::ACTIVATED_USER_ID,
        ]);

        // 受付後のステータスチェック
        // -------------------------------------------------------------
        // (期待値)
        // Reception            => accepted
        // ChargeRequestHistory => accepted_reception
        // -------------------------------------------------------------
        $reception = $this->findByReceptionId($receptionId);
        $this->assertSame(Reception::RECEPTION_STATUS_ACCEPTED, $reception->reception_status);
        $this->assertSame(ChargeRequestHistory::STATUS_ACCEPTED_RECEPTION, $reception->chargeRequestHistory->processing_status);
    }

    /**
     * カード会員番号での受付でフラグが正常に立つかどうかのテスト
     * - カード会員番号で受けつけた場合にフラグが立つかどうか
     */
    public function testReceptionByCardNumber()
    {
        // PolletID指定
        $receptionId = $this->executeReception([
            'pollet_user_id'=> self::ACTIVATED_USER_ID,
        ]);
        $reception = $this->findByReceptionId($receptionId);
        $this->assertTrue($reception->by_card_number == 0);

        // カード会員番号指定
        $receptionId = $this->executeReception([
            'card_number'=> self::ACTIVATED_USER_CARD_NO,
        ]);
        $reception = $this->findByReceptionId($receptionId);
        $this->assertTrue($reception->by_card_number == 1);

        // 双方指定
        $receptionId = $this->executeReception([
            'pollet_user_id'=> self::ACTIVATED_USER_ID,
            'card_number'=> self::ACTIVATED_USER_CARD_NO,
        ]);
        $reception = $this->findByReceptionId($receptionId);
        $this->assertTrue($reception->by_card_number == 1);
    }

    /**
     * 申請API用モデルの動作テスト(新規ユーザ)
     * - 申請を正常に行えるかどうか
     */
    public function testApplyForNewUser()
    {
        $receptionId = $this->executeReception([
            'pollet_user_id'=> self::NEW_USER_ID,
        ]);

        // 申請
        $applyParams = $this->generateApplyParameters($receptionId);
        $this->inquire->siteAuthorize = $this->siteAuth;
        $this->assertTrue($this->inquire->load($applyParams));
        $this->assertTrue($this->inquire->apply());
    }

    /**
     * 申請API用モデルの動作テスト(アクティベート済みユーザ)
     * - 申請を正常に行えるかどうか
     */
    public function testApplyForActivatedUser()
    {
        $receptionId = $this->executeReception([
            'pollet_user_id'=> self::ACTIVATED_USER_ID,
        ]);

        // 申請
        $applyParams = $this->generateApplyParameters($receptionId);
        $this->inquire->siteAuthorize = $this->siteAuth;
        $this->assertTrue($this->inquire->load($applyParams));
        $this->assertTrue($this->inquire->apply());
    }

    /**
     * ユーザ整合性チェック用モデルの動作テスト(新規ユーザ)
     * - ユーザ整合性チェックを正常に行えるかどうか
     */
    public function testConsistencyForNewUser()
    {
        $receptionId = $this->executeReception([
            'pollet_user_id'=> self::NEW_USER_ID,
        ]);

        $reception = $this->findByReceptionId($receptionId);

        // ユーザ整合性チェック
        $consistencyParams = $this->generateConsistencyParameters($receptionId);
        $this->consistency->user = $reception->polletUser;
        $this->assertTrue($this->consistency->load($consistencyParams));
        $this->assertTrue($this->consistency->check());
        $this->assertTrue($this->consistency->updateStatuses());
    }

    /**
     * ユーザ整合性チェック用モデルの動作テスト(アクティベート済みユーザ)
     * - ユーザ整合性チェックを正常に行えるかどうか
     */
    public function testConsistencyForActivatedUser()
    {
        $receptionId = $this->executeReception([
            'pollet_user_id'=> self::ACTIVATED_USER_ID,
        ]);

        $reception = $this->findByReceptionId($receptionId);

        // ユーザ整合性チェック
        $consistencyParams = $this->generateConsistencyParameters($receptionId);
        $this->consistency->user = $reception->polletUser;
        $this->assertTrue($this->consistency->load($consistencyParams));
        $this->assertTrue($this->consistency->check());
        $this->assertTrue($this->consistency->updateStatuses());
    }

    /**
     * 新規ユーザが 受付 => 申請 => ユーザ整合性チェック をした時のステータス遷移テスト
     * - 仕様通りのステータス遷移を行うかどうか
     */
    public function testStatusesNewUserApplyToConsistency()
    {
        $result = $this->executeApplyToConsistency([
            'pollet_user_id'=> self::NEW_USER_ID,
        ]);

        // 申請後のステータスチェック
        // -------------------------------------------------------------
        // (期待値)
        // Reception            => applied
        // ChargeRequestHistory => accepted_reception
        // -------------------------------------------------------------
        $this->assertSame(Reception::RECEPTION_STATUS_APPLIED, $result[self::APPLIED_RECEPTION]);
        $this->assertSame(ChargeRequestHistory::STATUS_ACCEPTED_RECEPTION, $result[self::APPLIED_CHARGE_REQ]);

        // ユーザ整合性チェック後のステータスチェック
        // -------------------------------------------------------------
        // (期待値)
        // Reception            => applied
        // ChargeRequestHistory => unprocessed_first_charge
        // -------------------------------------------------------------
        $this->assertSame(Reception::RECEPTION_STATUS_APPLIED, $result[self::CONSISTENCED_RECEPTION]);
        $this->assertSame(ChargeRequestHistory::STATUS_UNPROCESSED_FIRST_CHARGE, $result[self::CONSISTENCED_CHARGE_REQ]);

        // ユーザステータスチェック
        // -------------------------------------------------------------
        // (期待値)
        // PolletUser => charge_requested
        // -------------------------------------------------------------
        $this->assertSame(PolletUser::STATUS_CHARGE_REQUESTED, $result[self::USER_STATUS]);
    }

    /**
     * 新規ユーザが 受付 => ユーザ整合性チェック => 申請 をした時のステータス遷移テスト
     * - 仕様通りのステータス遷移を行うかどうか
     */
    public function testStatusesNewUserConsistencyToApply()
    {
        $result = $this->executeConsistencyToApply([
            'pollet_user_id'=> self::NEW_USER_ID,
        ]);

        // ユーザ整合性チェック後のステータスチェック
        // -------------------------------------------------------------
        // (期待値)
        // Reception            => appcepted
        // ChargeRequestHistory => wainting_apply
        // -------------------------------------------------------------
        $this->assertSame(Reception::RECEPTION_STATUS_ACCEPTED, $result[self::CONSISTENCED_RECEPTION]);
        $this->assertSame(ChargeRequestHistory::STATUS_WAITING_APPLY, $result[self::CONSISTENCED_CHARGE_REQ]);

        // 申請後のステータスチェック
        // -------------------------------------------------------------
        // (期待値)
        // Reception            => applied
        // ChargeRequestHistory => unprocessed_first_charge
        // -------------------------------------------------------------
        $this->assertSame(Reception::RECEPTION_STATUS_APPLIED, $result[self::APPLIED_RECEPTION]);
        $this->assertSame(ChargeRequestHistory::STATUS_UNPROCESSED_FIRST_CHARGE, $result[self::APPLIED_CHARGE_REQ]);

        // ユーザステータスチェック
        // -------------------------------------------------------------
        // (期待値)
        // PolletUser => charge_requested
        // -------------------------------------------------------------
        $this->assertSame(PolletUser::STATUS_CHARGE_REQUESTED, $result[self::USER_STATUS]);
    }

    /**
     * アクティベート済みユーザが 受付 => 申請 => ユーザ整合性チェック をした時のステータス遷移テスト
     * - 仕様通りのステータス遷移を行うかどうか
     */
    public function testStatusesActivatedUserApplyToConsistency()
    {
        $result = $this->executeApplyToConsistency([
            'pollet_user_id'=> self::ACTIVATED_USER_ID,
        ]);

        // 申請後のステータスチェック
        // -------------------------------------------------------------
        // (期待値)
        // Reception            => applied
        // ChargeRequestHistory => accepted_reception
        // -------------------------------------------------------------
        $this->assertSame(Reception::RECEPTION_STATUS_APPLIED, $result[self::APPLIED_RECEPTION]);
        $this->assertSame(ChargeRequestHistory::STATUS_ACCEPTED_RECEPTION, $result[self::APPLIED_CHARGE_REQ]);

        // ユーザ整合性チェック後のステータスチェック
        // -------------------------------------------------------------
        // (期待値)
        // Reception            => applied
        // ChargeRequestHistory => ready
        // -------------------------------------------------------------
        $this->assertSame(Reception::RECEPTION_STATUS_APPLIED, $result[self::CONSISTENCED_RECEPTION]);
        $this->assertSame(ChargeRequestHistory::STATUS_READY, $result[self::CONSISTENCED_CHARGE_REQ]);

        // ユーザステータスチェック
        // -------------------------------------------------------------
        // (期待値)
        // PolletUser => activated
        // -------------------------------------------------------------
        $this->assertSame(PolletUser::STATUS_ACTIVATED, $result[self::USER_STATUS]);
    }

    /**
     * アクティベート済みユーザが 受付 => ユーザ整合性チェック => 申請 をした時のステータス遷移テスト
     * - 仕様通りのステータス遷移を行うかどうか
     */
    public function testStatusesActivatedUserConsistencyToApply()
    {
        $result = $this->executeConsistencyToApply([
            'pollet_user_id'=> self::ACTIVATED_USER_ID,
        ]);

        // ユーザ整合性チェック後のステータスチェック
        // -------------------------------------------------------------
        // (期待値)
        // Reception            => appcepted
        // ChargeRequestHistory => wainting_apply
        // -------------------------------------------------------------
        $this->assertSame(Reception::RECEPTION_STATUS_ACCEPTED, $result[self::CONSISTENCED_RECEPTION]);
        $this->assertSame(ChargeRequestHistory::STATUS_WAITING_APPLY, $result[self::CONSISTENCED_CHARGE_REQ]);

        // 申請後のステータスチェック
        // -------------------------------------------------------------
        // (期待値)
        // Reception            => applied
        // ChargeRequestHistory => ready
        // -------------------------------------------------------------
        $this->assertSame(Reception::RECEPTION_STATUS_APPLIED, $result[self::APPLIED_RECEPTION]);
        $this->assertSame(ChargeRequestHistory::STATUS_READY, $result[self::APPLIED_CHARGE_REQ]);

        // ユーザステータスチェック
        // -------------------------------------------------------------
        // (期待値)
        // PolletUser => activated
        // -------------------------------------------------------------
        $this->assertSame(PolletUser::STATUS_ACTIVATED, $result[self::USER_STATUS]);
    }

    /**
     * アクティベート済みユーザが カード会員番号で受付 => 申請 => ユーザ整合性チェック をした時のステータス遷移テスト
     * - 仕様通りのステータス遷移を行うかどうか
     */
    public function testStatusesCardNumberActivatedUserApplyToConsistency()
    {
        $result = $this->executeApplyToConsistency([
            'card_number'=> self::ACTIVATED_USER_CARD_NO,
        ]);

        // 申請後のステータスチェック
        // -------------------------------------------------------------
        // (期待値)
        // Reception            => applied
        // ChargeRequestHistory => ready
        // -------------------------------------------------------------
        $this->assertSame(Reception::RECEPTION_STATUS_APPLIED, $result[self::APPLIED_RECEPTION]);
        $this->assertSame(ChargeRequestHistory::STATUS_READY, $result[self::APPLIED_CHARGE_REQ]);

        // ユーザ整合性チェック後のステータスチェック
        // -------------------------------------------------------------
        // (期待値)
        // Reception            => applied
        // ChargeRequestHistory => ready
        // -------------------------------------------------------------
        $this->assertSame(Reception::RECEPTION_STATUS_APPLIED, $result[self::CONSISTENCED_RECEPTION]);
        $this->assertSame(ChargeRequestHistory::STATUS_READY, $result[self::CONSISTENCED_CHARGE_REQ]);

        // ユーザステータスチェック
        // -------------------------------------------------------------
        // (期待値)
        // PolletUser => activated
        // -------------------------------------------------------------
        $this->assertSame(PolletUser::STATUS_ACTIVATED, $result[self::USER_STATUS]);
    }

    /**
     * アクティベート済みユーザが カード会員番号で受付 => ユーザ整合性チェック => 申請 をした時のステータス遷移テスト
     * - 仕様通りのステータス遷移を行うかどうか
     */
    public function testStatusesCardNumberActivatedUserConsistencyToApply()
    {
        $result = $this->executeConsistencyToApply([
            'card_number'=> self::ACTIVATED_USER_CARD_NO,
        ]);

        // ユーザ整合性チェック後のステータスチェック
        // -------------------------------------------------------------
        // (期待値)
        // Reception            => appcepted
        // ChargeRequestHistory => wainting_apply
        // -------------------------------------------------------------
        $this->assertSame(Reception::RECEPTION_STATUS_ACCEPTED, $result[self::CONSISTENCED_RECEPTION]);
        $this->assertSame(ChargeRequestHistory::STATUS_WAITING_APPLY, $result[self::CONSISTENCED_CHARGE_REQ]);

        // 申請後のステータスチェック
        // -------------------------------------------------------------
        // (期待値)
        // Reception            => applied
        // ChargeRequestHistory => ready
        // -------------------------------------------------------------
        $this->assertSame(Reception::RECEPTION_STATUS_APPLIED, $result[self::APPLIED_RECEPTION]);
        $this->assertSame(ChargeRequestHistory::STATUS_READY, $result[self::APPLIED_CHARGE_REQ]);

        // ユーザステータスチェック
        // -------------------------------------------------------------
        // (期待値)
        // PolletUser => activated
        // -------------------------------------------------------------
        $this->assertSame(PolletUser::STATUS_ACTIVATED, $result[self::USER_STATUS]);
    }

    /**
     *
     * @param array $margeParams
     * @return array
     */
    private function executeSiteAuthorize(array $margeParams) : array
    {
        $receptionParams = $this->generateReceptionParameters($margeParams);
        $this->siteAuth->load($receptionParams);
        $this->siteAuth->authorize();

        return $receptionParams;
    }

    /**
     *
     * @param array $margeParams
     * @return string
     */
    private function executeReception(array $margeParams) : string
    {
        // サイト認証
        $receptionParams = $this->executeSiteAuthorize($margeParams);
        $receptionParams['charge_source_code'] = $this->siteAuth->getChargeSource()->charge_source_code;

        // 受付
        $this->reception->setScenario(Reception::SCENARIO_API_REQUEST);
        $this->reception->siteAuthorize = $this->siteAuth;
        $this->reception->load($receptionParams);
        $this->reception->accept();

        return $this->reception->reception_code;
    }

    /**
     * 申請を実行
     * @param string $receptionId 受付ID
     */
    private function executeApply(string $receptionId)
    {
        $applyParams = $this->generateApplyParameters($receptionId);
        $this->inquire->siteAuthorize = $this->siteAuth;
        $this->inquire->load($applyParams);
        $this->inquire->apply();
    }

    /**
     * 状態確認を実行
     * @param string[] $receptionIds 受付IDの配列
     * @return array 結果
     */
    private function executeInquire(array $receptionIds)
    {
        $applyParams = $this->generateApplyParameters($receptionIds);
        $this->inquire->siteAuthorize = $this->siteAuth;
        $this->inquire->load($applyParams);
        $this->inquire->inquire();
        return $this->inquire->getResults();
    }

    /**
     * ユーザ整合性チェックを実行
     * @param string $receptionId
     * @param PolletUser $user
     */
    private function executeConsistency(string $receptionId, PolletUser $user)
    {
        $consistencyParams = $this->generateConsistencyParameters($receptionId);
        $this->consistency->user = $user;
        $this->consistency->load($consistencyParams);
        $this->consistency->check();
        $this->consistency->updateStatuses();
    }

    /**
     * 受付 => 申請 => ユーザ整合性チェック を行い、各時点でのステータス結果を取得する
     * @param array $margeParams 受付API用のパラメータ配列にマージするデータ
     * @return array 各時点でのステータス結果を取得する
     */
    private function executeApplyToConsistency(array $margeParams)
    {
        $receptionId = $this->executeReception($margeParams);

        // 申請
        $this->executeApply($receptionId);
        $reception = $this->findByReceptionId($receptionId);
        $ret[self::APPLIED_RECEPTION]  = $reception->reception_status;
        $ret[self::APPLIED_CHARGE_REQ] = $reception->chargeRequestHistory->processing_status;

        // ユーザ整合性チェック
        $this->executeConsistency($receptionId, $reception->polletUser);
        $reception = $this->findByReceptionId($receptionId);
        $ret[self::CONSISTENCED_RECEPTION]  = $reception->reception_status;
        $ret[self::CONSISTENCED_CHARGE_REQ] = $reception->chargeRequestHistory->processing_status;

        // 最終的なユーザステータス
        $reception = $this->findByReceptionId($receptionId);
        $ret[self::USER_STATUS] = $reception->polletUser->registration_status;

        return $ret;
    }

    /**
     * 受付 => ユーザ整合性チェック => 申請 を行い、各時点でのステータス結果を取得する
     * @param array $margeParams 受付API用のパラメータ配列にマージするデータ
     * @return array 各時点でのステータス結果を取得する
     */
    private function executeConsistencyToApply(array $margeParams)
    {
        $receptionId = $this->executeReception($margeParams);

        // ユーザ整合性チェック
        $reception = $this->findByReceptionId($receptionId);
        $this->executeConsistency($receptionId, $reception->polletUser);
        $reception = $this->findByReceptionId($receptionId);
        $ret[self::CONSISTENCED_RECEPTION]  = $reception->reception_status;
        $ret[self::CONSISTENCED_CHARGE_REQ] = $reception->chargeRequestHistory->processing_status;

        // 申請
        $this->executeApply($receptionId);
        $reception = $this->findByReceptionId($receptionId);
        $ret[self::APPLIED_RECEPTION]  = $reception->reception_status;
        $ret[self::APPLIED_CHARGE_REQ] = $reception->chargeRequestHistory->processing_status;

        // 最終的なユーザステータス
        $reception = $this->findByReceptionId($receptionId);
        $ret[self::USER_STATUS] = $reception->polletUser->registration_status;

        return $ret;
    }

    /**
     * @param string $receptionId
     * @return Reception|null|array
     */
    private function findByReceptionId($receptionId)
    {
        return Reception::find()->receptionId($receptionId)->one();
    }

    /**
     * 受付API用のパラメータ配列を生成する
     * @param array $margeParams マージする値
     * @return array 申請API用のパラメータ配列
     */
    private function generateReceptionParameters(array $margeParams)
    {
        return array_merge([
            'site_code'     => self::SITE_CODE,
            'api_key'       => self::API_KEY,
            'card_number'   => '',
            'pollet_user_id'=> '',
            'amount'        => 1000,
            'delay'         => 1,
        ], $margeParams);
    }

    /**
     * 申請API用のパラメータ配列を生成する
     * @param string|array $receptionIds 受付ID(または受付IDの配列)
     * @return array 申請API用のパラメータ配列
     */
    private function generateApplyParameters($receptionIds)
    {
        $csv = '';
        if (is_array($receptionIds)) {
            $csv = implode(',', $receptionIds);
        } elseif (is_string($receptionIds)) {
            $csv = $receptionIds;
        }

        return [
            'site_code'     => self::SITE_CODE,
            'api_key'       => self::API_KEY,
            'reception_ids' => $csv,
        ];
    }

    /**
     * ユーザ整合性チェック用のパラメータ配列を生成する
     * @param string $receptionId 受付ID
     * @return array ユーザ整合性チェック用のパラメータ配列
     */
    private function generateConsistencyParameters($receptionId)
    {
        return [
            'reception_id' => $receptionId,
        ];
    }

    /**
     * 後処理
     */
    protected function _after()
    {
        // 終了後に実行する処理
    }
}