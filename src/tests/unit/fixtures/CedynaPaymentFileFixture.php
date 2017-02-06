<?php
namespace tests\unit\fixtures;

use app\models\ChargeRequestHistory;
use app\models\ChargeSource;
use app\models\PaymentFileDeliveryManager;
use app\models\PolletUser;
use Faker;
use yii\helpers\FileHelper;
use yii;

class CedynaPaymentFileFixture extends PolletDbFixture
{
    public $HULFT配信用ディレクトリ;
    public $作業ディレクトリ;
    public $完了ディレクトリ;

    /**
     * 初期化
     */
    public function init()
    {
        $this->HULFT配信用ディレクトリ = Yii::$app->params['hulftPath'] . '/send';
        $this->作業ディレクトリ = Yii::$app->params['hulftPath'] . '/app/send_payment_file/processing';
        $this->完了ディレクトリ = Yii::$app->params['hulftPath'] . '/app/send_payment_file/complete';
    }

    /**
     * make-cedyna-payment-fileバッチで使用するテストデータの作成
     */
    public function load()
    {
        // 共通で使うチャージ元
        $chargeSource = $this->createChargeSource();
        // ファイル伝送管理のデータ作成 伝送中じゃない状態だとバッチ起動ができる
        $this->createSendingPaymentFileManager();

        // 入金ファイル作成中のユーザー1
        $user = $this->createFinishedFirstChargeUser(10001);
        $this->createChargeRequest(100001, ChargeRequestHistory::STATUS_IS_MAKING_PAYMENT_FILE, $user, $chargeSource);

        // 入金ファイル作成中のユーザー2
        $user = $this->createFinishedFirstChargeUser(10002);
        $this->createChargeRequest(100002, ChargeRequestHistory::STATUS_IS_MAKING_PAYMENT_FILE, $user, $chargeSource);

        // 処理待ちのユーザー1
        $user = $this->createFinishedFirstChargeUser(10003);
        $this->createChargeRequest(100003, ChargeRequestHistory::STATUS_READY, $user, $chargeSource);

    }

    /**
     * make-cedyna-payment-fileバッチで使用するテストデータの作成（複数ユーザー対象）
     */
    public function loadMultipleReadyUsers()
    {
        // 共通で使うチャージ元
        $chargeSource = $this->createChargeSource();
        // ファイル伝送管理のデータ作成 伝送中じゃない状態だとバッチ起動ができる
        $this->createSendingPaymentFileManager();

        // 処理待ちのユーザー1
        $user = $this->createFinishedFirstChargeUser(10011);
        $this->createChargeRequest(100011, ChargeRequestHistory::STATUS_READY, $user, $chargeSource);

        // 処理待ちのユーザー2
        $user = $this->createFinishedFirstChargeUser(10012);
        $this->createChargeRequest(100012, ChargeRequestHistory::STATUS_READY, $user, $chargeSource);
    }

    /**
     * make-cedyna-payment-fileバッチで使用するテストデータの作成(ファイル伝送中の状態を再現)
     */
    public function loadSendingPaymentFile()
    {
        // 共通で使うチャージ元
        $chargeSource = $this->createChargeSource();
        // ファイル伝送管理のデータ作成 伝送中状態のデータをつくる
        $this->createSendingPaymentFileManager(1);

        // 処理待ちのユーザー1
        $user = $this->createFinishedFirstChargeUser(10003);
        $this->createChargeRequest(100003, ChargeRequestHistory::STATUS_READY, $user, $chargeSource);

    }

    /**
     * make-cedyna-payment-fileバッチで使用するテストデータの作成(ユーザーデータが存在しない)
     */
    public function loadEmptyUserData()
    {
        // 共通で使うチャージ元
        $this->createChargeSource();
        // ファイル伝送管理のデータ作成 伝送中じゃない状態のデータをつくる
        $this->createSendingPaymentFileManager();
    }

    /**
     * make-cedyna-payment-fileバッチで使用するディレクトリの削除
     * @throws yii\base\ErrorException
     */
    public function removeDir()
    {
        FileHelper::removeDirectory($this->HULFT配信用ディレクトリ);
        FileHelper::removeDirectory($this->作業ディレクトリ);
        FileHelper::removeDirectory($this->完了ディレクトリ);
    }

    /**
     * @return ChargeSource
     */
    protected function createChargeSource()
    {
        $chargeSource = new ChargeSource();
        $chargeSource->charge_source_code = 'testcharge';
        $chargeSource->site_name = 'testcharge';
        $chargeSource->min_value = 300;
        $chargeSource->card_issue_fee = 0;
        $chargeSource->url = 'http://testcharge.com/';
        $chargeSource->introduce_charge_rate_point = 1;
        $chargeSource->introduce_charge_rate_price = 1;
        $chargeSource->description = 'testcharge';
        $chargeSource->publishing_status = 'public';
        $chargeSource->cooperation_type = ChargeSource::COOPERATION_TYPE_POINT_SITE_API;
        $chargeSource->save();

        return $chargeSource;
    }

    /**
     * @param int $polletId
     * @return PolletUser
     */
    protected function createFinishedFirstChargeUser(int $polletId)
    {
        $faker = Faker\Factory::create();
        $user = new PolletUser();
        $user->id = $polletId;
        $user->user_code_secret = $faker->md5;
        $user->cedyna_id = $faker->regexify('[0-9]{16}');
        $user->mail_address = $faker->email;
        $user->registration_status = 'finished_first_charge';
        $user->balance_at_charge = 0;
        $user->save();

        return $user;
    }

    /**
     * @param int $id
     * @param string $processingStatus
     * @param PolletUser $user
     * @param ChargeSource $chargeSource
     * @return ChargeRequestHistory
     */
    protected function createChargeRequest(int $id, string $processingStatus, PolletUser $user, ChargeSource $chargeSource)
    {
        $chargeRequestHistory = new ChargeRequestHistory();
        $chargeRequestHistory->id = $id;
        $chargeRequestHistory->pollet_user_id = $user->id;
        $chargeRequestHistory->charge_source_code = $chargeSource->charge_source_code;
        $chargeRequestHistory->exchange_value = 1000;
        $chargeRequestHistory->charge_value = 1000 - $chargeSource->card_issue_fee;
        $chargeRequestHistory->processing_status = $processingStatus;
        $chargeRequestHistory->cause = 'テストチャージ';
        $chargeRequestHistory->save();

        return $chargeRequestHistory;
    }

    /**
     * @param int $isSending
     * @return PaymentFileDeliveryManager
     * @throws \Exception
     */
    protected function createSendingPaymentFileManager($isSending = 0)
    {
        // テストデータ初期化
        $records = PaymentFileDeliveryManager::find()->all();
        foreach($records as $record) {
            $record->delete();
        }

        $paymentFileDeliveryManager = new PaymentFileDeliveryManager();
        $paymentFileDeliveryManager->is_sending = $isSending;
        $paymentFileDeliveryManager->save();

        return $paymentFileDeliveryManager;
    }
}
