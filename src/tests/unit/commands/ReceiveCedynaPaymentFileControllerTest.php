<?php

namespace tests\unit\commands;

use app\components\HulftDummy;
use app\helpers\Date;
use app\models\cedyna_files\CedynaFile;
use app\models\ChargeRequestHistory;
use app\models\exceptions\Hulft\ReceivingException;
use app\models\PaymentFileDeliveryManager;
use app\models\PointSiteApi;
use linslin\yii2\curl\Curl;
use tests\unit\fixtures\ReceiveCedynaPaymentFileFixture;
use Yii;
use yii\codeception\TestCase;

/**
 * Class ReceiveCedynaPaymentFileControllerTest
 * @package tests\unit\commands
 * @property ReceiveCedynaPaymentFileFixture $fixture
 */
class ReceiveCedynaPaymentFileControllerTest extends TestCase
{
    public $appConfig = '@app/config/console.php';

    public function setUp()
    {
        parent::setUp();

        $this->setHulftMock(true);
    }

    public function fixtures()
    {
        return [
            'fixture' => ReceiveCedynaPaymentFileFixture::class,
        ];
    }

    private function runBatch()
    {
        Yii::$app->runAction('receive-cedyna-payment-file');
    }

    /**
     * バッチの中で HULFT を使ったら呼ばれるモックを設定する。
     *
     * @param bool $succeeds 送信が成功するかどうか
     */
    private function setHulftMock(bool $succeeds)
    {
        if (!$succeeds) {
            $mock = $this->createMock(HulftDummy::class);
            // 失敗したら例外が投げられるはず
            $mock->method('receiveCedynaPaymentFileSync')
                ->willThrowException(new ReceivingException());
            Yii::$app->set('hulft', $mock);
        }
    }

    /**
     * @test
     */
    public function 入金ファイルを送っていないときは実行しない()
    {
        $this->fixture->loadNotSendingPaymentFile();
        $receivedFileHash = md5_file("{$this->fixture->receivedFilesDirectory}/{$this->fixture->receivedFileName}");

        $this->runBatch();

        // 受け取りディレクトリに残っている
        $receiveFiles = CedynaFile::findAll($this->fixture->receivedFilesDirectory);
        $this->assertCount(1, $receiveFiles);
        $this->assertEquals($receivedFileHash, md5_file($receiveFiles[0]->getPath()));
        // 処理完了ディレクトリに移動していない
        $completeFiles = CedynaFile::findAll($this->fixture->completeFilesDirectory);
        $this->assertCount(0, $completeFiles);
        // 入金ファイル伝送状態は伝送中じゃないまま
        $this->assertEquals(false, PaymentFileDeliveryManager::isSending());
    }

    /**
     * @test
     */
    public function ファイルの処理が成功した場合処理完了ディレクトリに移動する()
    {
        $receivedFileHash = md5_file("{$this->fixture->receivedFilesDirectory}/{$this->fixture->receivedFileName}");

        $this->runBatch();

        // 処理完了ディレクトリに移動している
        $completeFiles = CedynaFile::findAll($this->fixture->completeFilesDirectory);
        $this->assertCount(1, $completeFiles);
        $this->assertEquals($receivedFileHash, md5_file($completeFiles[0]->getPath()));

        // ファイルを受け取るディレクトリからなくなってる
        $this->assertCount(0, CedynaFile::findAll($this->fixture->receivedFilesDirectory));

        // エラーディレクトリには入ってない
        $this->assertCount(0, CedynaFile::findAll($this->fixture->formatErrorFilesDirectory));
    }

    /**
     * @test
     */
    public function ファイルの処理が成功した場合入金ファイル伝送状態は伝送中じゃない状態になっている()
    {
        $receivedFileHash = md5_file("{$this->fixture->receivedFilesDirectory}/{$this->fixture->receivedFileName}");

        $this->runBatch();

        // 入金ファイル伝送状態が伝送中じゃなくなっている
        $this->assertEquals(false, PaymentFileDeliveryManager::isSending());
        // 処理完了ディレクトリに移動している
        $completeFiles = CedynaFile::findAll($this->fixture->completeFilesDirectory);
        $this->assertCount(1, $completeFiles);
        $this->assertEquals($receivedFileHash, md5_file($completeFiles[0]->getPath()));
    }

    /**
     * @test
     */
    public function フォーマットエラーのファイルを受け取った場合エラーディレクトリに移動する()
    {
        $this->fixture->makeReceivedErrorCsv();

        $receivedFileHash = md5_file("{$this->fixture->receivedFilesDirectory}/{$this->fixture->receivedFileName}");

        $this->runBatch();

        // エラーディレクトリに移動している
        $completeFiles = CedynaFile::findAll($this->fixture->formatErrorFilesDirectory);
        $this->assertCount(1, $completeFiles);
        $this->assertEquals($receivedFileHash, md5_file($completeFiles[0]->getPath()));

        // ファイルを受け取るディレクトリからなくなってる
        $this->assertCount(0, CedynaFile::findAll($this->fixture->receivedFilesDirectory));

        // 処理完了ディレクトリには入ってない
        $this->assertCount(0, CedynaFile::findAll($this->fixture->completeFilesDirectory));
    }

    /**
     * @test
     */
    public function 処理完了ディレクトリに移動するときに既存のファイルを上書きしない()
    {
        $this->runBatch();
        $fileCountBeforeRunBatch = count(CedynaFile::findAll($this->fixture->completeFilesDirectory));

        // 次のバッチを実行
        Date::setTestNow((new Date())->addHour(1));
        $this->fixture->makeEmptyCsv();
        $this->runBatch();

        // 処理完了ディレクトリにファイルが1個増えてる＝前のファイルを上書きしてない
        $completeFiles = CedynaFile::findAll($this->fixture->completeFilesDirectory);
        $this->assertCount($fileCountBeforeRunBatch + 1, $completeFiles);

        // 時間を元に戻す
        Date::setTestNow((new Date())->addHour(-1));
    }

    /**
     * @test
     */
    public function チャージに成功したチャージ申請履歴の処理状態をチャージ適用済みに更新する()
    {
        $this->runBatch();

        $okChargeRequest = ChargeRequestHistory::find()->where([
            'id' => $this->fixture->okChargeRequestId,
        ])->one();
        $this->assertEquals(ChargeRequestHistory::STATUS_APPLIED_CHARGE, $okChargeRequest->processing_status);
    }

    /**
     * @test
     */
    public function チャージに失敗したチャージ申請履歴の処理状態をチャージエラーに更新する()
    {
        $this->runBatch();

        $ngChargeRequest = ChargeRequestHistory::find()->where([
            'id' => $this->fixture->ngChargeRequestId,
        ])->one();
        $this->assertEquals(ChargeRequestHistory::STATUS_ERROR, $ngChargeRequest->processing_status);
    }

    /**
     * @test
     */
    public function チャージに成功した場合チャージエラー履歴にレコードが入らない()
    {
        $this->runBatch();

        $okChargeRequest = ChargeRequestHistory::find()->where([
            'id' => $this->fixture->okChargeRequestId,
        ])->one();
        $this->assertEmpty($okChargeRequest->chargeErrorHistories);

    }
    /**
     * @test
     */
    public function チャージエラー履歴に失敗したチャージの情報が入る()
    {
        $this->runBatch();

        $ngChargeRequest = ChargeRequestHistory::find()->where([
            'id' => $this->fixture->ngChargeRequestId,
        ])->one();
        $this->assertCount(1, $ngChargeRequest->chargeErrorHistories);

        $chargeError = $ngChargeRequest->chargeErrorHistories[0];
        $this->assertEquals($this->fixture->ngChargeErrorCode, $chargeError->error_code);
        $this->assertEquals($this->fixture->ngChargeRow, $chargeError->raw_data);
    }

    /**
     * @test
     */
    public function 入金結果ファイルに含まれないチャージ申請履歴を更新しない()
    {
        $this->runBatch();

        $chargeRequest = ChargeRequestHistory::find()->where([
            'id' => $this->fixture->readyChargeRequestId,
        ])->one();
        $this->assertEquals(ChargeRequestHistory::STATUS_READY, $chargeRequest->processing_status);
    }

    /**
     * @test
     */
    public function チャージ申請エラー時にポイントサイトの交換キャンセルAPIが呼び出される()
    {
        $apiUrl = PointSiteApi::find()->where([
            'api_name'           => PointSiteApi::API_NAME_CANCEL_EXCHANGE,
            'charge_source_code' => $this->fixture->chargeSourceCode,
        ])->one()->url;

        // モックを設定
        $curlMock = $this->createPartialMock(Curl::class, ['delete']);
        Yii::$app->set('curl', $curlMock);

        // 交換キャンセルAPIが呼ばれることをassertする
        $curlMock->expects($this->once()) // 1回のみ
            ->method('delete')
            ->with($this->stringStartsWith($apiUrl));

        $this->runBatch();
    }
}