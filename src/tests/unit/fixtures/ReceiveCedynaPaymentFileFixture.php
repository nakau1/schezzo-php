<?php
namespace tests\unit\fixtures;

use app\models\ChargeRequestHistory;
use app\models\ChargeSource;
use app\models\PointSiteApi;
use app\models\PolletUser;
use Faker;
use Yii;
use yii\helpers\FileHelper;

class ReceiveCedynaPaymentFileFixture extends CedynaPaymentFileFixture
{
    /** @var Faker\Generator */
    private $faker;

    public $receivedFilesDirectory;
    public $processingFilesDirectory;
    public $completeFilesDirectory;
    public $formatErrorFilesDirectory;

    public $receivedFilePath;
    public $receivedFileName;

    public $chargeSourceCode = 'testcharge';
    public $cedynaId = '0000001234567890';
    public $okChargeRequestId = 1001;
    public $ngChargeRequestId = 1002;
    public $readyChargeRequestId = 1003;
    public $notExistsChargeRequestId = 1004;

    public $ngChargeErrorCode;
    public $ngChargeRow;

    public function init()
    {
        $this->faker = Faker\Factory::create();

        $this->receivedFilesDirectory = Yii::$app->params['hulftPath'].'/recv/';
        $this->receivedFileName = 'scdpol01.txt';
        $this->receivedFilePath = "{$this->receivedFilesDirectory}/{$this->receivedFileName}";

        $this->processingFilesDirectory = Yii::$app->params['hulftPath'].'/app/receive_payment_file/processing';
        $this->completeFilesDirectory = Yii::$app->params['hulftPath'].'/app/receive_payment_file/complete';
        $this->formatErrorFilesDirectory = Yii::$app->params['hulftPath'].'/app/receive_payment_file/format_error';

        $this->ngChargeErrorCode = 4151;
        $this->ngChargeRow = '"D","0421","CEDYNA","提携先ごとに採番","0001xxxx","'.$this->cedynaId.'","'.$this->cedynaId.'","","1234","ハピタスからチャージ","1","'.$this->ngChargeErrorCode.'","'.$this->ngChargeRequestId.'"';
    }

    public function unload()
    {
        FileHelper::removeDirectory($this->receivedFilesDirectory);
        FileHelper::removeDirectory($this->processingFilesDirectory);
        FileHelper::removeDirectory($this->completeFilesDirectory);
        FileHelper::removeDirectory($this->formatErrorFilesDirectory);

        parent::unload();
    }

    public function load()
    {
        FileHelper::createDirectory($this->receivedFilesDirectory);
        FileHelper::createDirectory($this->processingFilesDirectory);
        FileHelper::createDirectory($this->completeFilesDirectory);
        FileHelper::createDirectory($this->formatErrorFilesDirectory);

        $this->makeReceivedCsv();

        // 共通で使うチャージ元
        $chargeSource = $this->createChargeSource();
        // 入金ファイル伝送中の状態をつくる
        $this->createSendingPaymentFileManager(1);

        $user = $this->createActivatedUser(101, $this->cedynaId);
        // 成功のチャージ申請
        $this->createChargeRequest(
            $this->okChargeRequestId,
            'requested_charge',
            $user,
            $chargeSource
        );
        // 失敗するチャージ申請
        $this->createChargeRequest(
            $this->ngChargeRequestId,
            'requested_charge',
            $user,
            $chargeSource
        );
        // チャージ処理待状態のチャージ申請
        $this->createChargeRequest(
            $this->readyChargeRequestId,
            ChargeRequestHistory::STATUS_READY,
            $user,
            $chargeSource
        );
    }

    public function loadNotSendingPaymentFile()
    {
        FileHelper::createDirectory($this->receivedFilesDirectory);
        FileHelper::createDirectory($this->processingFilesDirectory);
        FileHelper::createDirectory($this->completeFilesDirectory);
        FileHelper::createDirectory($this->formatErrorFilesDirectory);

        $this->makeReceivedCsv();

        // 共通で使うチャージ元
        $this->createChargeSource();
        // 入金ファイル伝送中じゃない状態をつくる
        $this->createSendingPaymentFileManager(0);
    }

    public function makeReceivedCsv()
    {
        $csv = <<<CSV
"S","YYYY/MM/DD hh:mm:ss"
"H","入金種別","イシュアコード","提携先コード","カード種別区分","会員グループ番号","会員番号","カードID","入金額","加盟店名（チャージ理由）","処理結果","エラーコード","処理番号",""
"D","0421","CEDYNA","提携先ごとに採番","0001xxxx","{$this->cedynaId}","{$this->cedynaId}","",1234,"ハピタスからチャージ（0.5％込）","0","","{$this->okChargeRequestId}"
{$this->ngChargeRow}
"D","0421","CEDYNA","提携先ごとに採番","0001xxxx","{$this->cedynaId}","{$this->cedynaId}","",1234,"ハピタスからチャージ（0.5％込）","0","","{$this->notExistsChargeRequestId}"
"E","      3"
CSV;
        file_put_contents($this->receivedFilePath, mb_convert_encoding($csv, 'SJIS'));
    }

    public function makeReceivedErrorCsv()
    {
        $csv = <<<CSV
"S","YYYY/MM/DD hh:mm:ss"
"H","入金種別","イシュアコード","提携先コード","カード種別区分","会員グループ番号","会員番号","カードID","入金額","加盟店名（チャージ理由）","処理結果","エラーコード","処理番号","A006"
"不正なデータ"
CSV;
        file_put_contents($this->receivedFilePath, mb_convert_encoding($csv, 'SJIS'));
    }

    public function makeEmptyCsv()
    {
        // バッチ処理開始のために入金ファイル伝送中の状態をつくる
        $this->createSendingPaymentFileManager(1);

        $csv = <<<CSV
"S","YYYY/MM/DD hh:mm:ss"
"H","入金種別","イシュアコード","提携先コード","カード種別区分","会員グループ番号","会員番号","カードID","入金額","加盟店名（チャージ理由）","処理結果","エラーコード","処理番号",""
"E","      0"
CSV;
        file_put_contents($this->receivedFilePath, mb_convert_encoding($csv, 'SJIS'));
    }

    /**
     * @return ChargeSource
     */
    protected function createChargeSource()
    {
        $chargeSource = new ChargeSource();
        $chargeSource->charge_source_code = $this->chargeSourceCode;
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

        $api = new PointSiteApi();
        $api->charge_source_code = $chargeSource->charge_source_code;
        $api->api_name = PointSiteApi::API_NAME_CANCEL_EXCHANGE;
        $api->url = 'http://localhost/cancel_exchange';
        $api->publishing_status = PointSiteApi::PUBLISHING_STATUS_PUBLIC;
        $api->save();

        return $chargeSource;
    }

    /**
     * @param int $polletId
     * @param string $cedynaId
     * @return PolletUser
     */
    protected function createActivatedUser(int $polletId, string $cedynaId)
    {
        $user = new PolletUser();
        $user->id = $polletId;
        $user->user_code_secret = $this->faker->md5;
        $user->cedyna_id = $cedynaId;
        $user->mail_address = $this->faker->email;
        $user->registration_status = PolletUser::STATUS_ACTIVATED;
        $user->balance_at_charge = 0;
        $user->save();

        return $user;
    }
}
