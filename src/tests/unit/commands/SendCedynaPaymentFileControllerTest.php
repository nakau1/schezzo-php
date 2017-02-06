<?php

namespace tests\unit\commands;

use app\components\HulftDummy;
use app\helpers\File;
use app\models\cedyna_files\CedynaFile;
use app\models\ChargeRequestHistory;
use app\models\exceptions\Hulft\SendingException;
use app\models\PaymentFileDeliveryManager;
use tests\unit\fixtures\BacthManagementFixture;
use tests\unit\fixtures\CedynaPaymentFileFixture;
use tests\unit\fixtures\CedynaPaymentFileWithoutReadyUserFixture;
use tests\unit\fixtures\PolletDbFixture;
use Yii;
use yii\codeception\TestCase;

/**
 * Class SendCedynaPaymentFileControllerTest
 * @package tests\unit\commands
 */
class SendCedynaPaymentFileControllerTest extends TestCase
{
    public $appConfig = '@app/config/console.php';
    private $batchName = 'send-cedyna-payment-file/index';

    public function setUp()
    {
        parent::setUp();

        $cedynaPaymentFileFixture = new CedynaPaymentFileFixture();
        $cedynaPaymentFileFixture->removeDir();

        $this->setHulftMock(true);
    }

    public function fixtures()
    {
        return [
            'fixture' => PolletDbFixture::class,
        ];
    }

    private function runBatch()
    {
        Yii::$app->runAction($this->batchName);
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
            $mock->method('sendCedynaPaymentFileSync')
                ->willThrowException(new SendingException());
            Yii::$app->set('hulft', $mock);
        }
    }

    /**
     * @test
     */
    public function 処理完了後に完了ディレクトリと配信用ディレクトリに入金ファイルを作成する()
    {
        //処理待ちのユーザーのデータを用意
        $cedynaPaymentFixture = new CedynaPaymentFileFixture();
        $cedynaPaymentFixture->load();

        $this->runBatch();

        // 中身のデータの担保は別のテストケースで行う
        $this->assertNotEmpty(CedynaFile::findAll($cedynaPaymentFixture->完了ディレクトリ));
        $this->assertNotEmpty(CedynaFile::findAll($cedynaPaymentFixture->HULFT配信用ディレクトリ));
        // 作業用ディレクトリにファイルが残ってない
        $this->assertEmpty(CedynaFile::findAll($cedynaPaymentFixture->作業ディレクトリ));
    }

    /**
     * @test
     */
    public function 入金ファイルに必要なデータが出力される()
    {
        $cedynaPaymentFixture = new CedynaPaymentFileFixture();
        $cedynaPaymentFixture->load();

        $this->runBatch();

        $chargeRequestHistory = ChargeRequestHistory::find()->where(['id' => 100003])->one();

        $file = CedynaFile::findAll($cedynaPaymentFixture->完了ディレクトリ)[0];
        // TODO: CedynaFile からは convert された状態で取得したい
        $row = array_map(function (string $column) {
            return mb_convert_encoding($column, 'UTF8', 'SJIS');
        }, $file->readDataLinesAll()->current());

        // セディナID(会員番号)
        $this->assertEquals($chargeRequestHistory->polletUser->cedyna_id, $row[5]);
        // セディナID(会員グループ番号)
        $this->assertEquals($chargeRequestHistory->polletUser->cedyna_id, $row[6]);
        // 入金額
        $this->assertEquals($chargeRequestHistory->charge_value, $row[8]);
        // チャージ理由
        $this->assertEquals($chargeRequestHistory->cause, $row[9]);
        // チャージ申請履歴ID
        $this->assertEquals($chargeRequestHistory->id, $row[12]);
    }

    /**
     * @test
     */
    public function 前回の伝送ファイルがまだ伝送中だった場合処理せず終了する()
    {
        //処理待ちユーザーの作成とファイル伝送中状態の再現
        $CedynaPaymentFileFixture = new CedynaPaymentFileFixture();
        $CedynaPaymentFileFixture->loadSendingPaymentFile();

        $this->runBatch();

        //入金ファイル作成待ちのユーザーが更新されていないことを確認
        $chargeRequestHistory = ChargeRequestHistory::find()->where(['id' => 100003])->one();
        $this->assertEquals(ChargeRequestHistory::STATUS_READY,
            $chargeRequestHistory->processing_status);
        //入金ファイル管理状態が伝送中のままなことを確認する
        $this->assertEquals(true, PaymentFileDeliveryManager::isSending());
    }

    /**
     * @test
     */
    public function 他プロセスで実行中だった場合終了する()
    {
        //バッチ管理テーブルを他プロセスで実行中の状態にする
        $bacthManagementFixture = new BacthManagementFixture($this->batchName);
        $bacthManagementFixture->load();
        //処理待ちユーザーの作成
        $CedynaPaymentFileFixture = new CedynaPaymentFileFixture();
        $CedynaPaymentFileFixture->load();

        $this->runBatch();

        //入金ファイル作成待ちのユーザーが更新されていないことを確認
        $chargeRequestHistory = ChargeRequestHistory::find()->where(['id' => 100003])->one();
        $this->assertEquals(ChargeRequestHistory::STATUS_READY,
            $chargeRequestHistory->processing_status);
    }

    /**
     * 前回出力したファイルが送信されていない
     * @test
     */
    public function 前回出力したファイルが送信されていない場合終了する()
    {
        $fixture = new CedynaPaymentFileFixture();
        $fixture->load();

        // 前回出力したファイル
        File::makeDirectoryIfNotExists($fixture->HULFT配信用ディレクトリ);
        $dummyFile = new CedynaFile($fixture->HULFT配信用ディレクトリ.'/rcdpol01.txt');
        $dummyFile->setSaveContent('前回出力した内容')->save();

        $this->runBatch();

        // データが更新されていない
        $chargeRequestHistory = ChargeRequestHistory::find()->where(['id' => 100003])->one();
        $this->assertEquals(ChargeRequestHistory::STATUS_READY, $chargeRequestHistory->processing_status);
        // データが出力されていない
        $this->assertEmpty(CedynaFile::findAll($fixture->完了ディレクトリ));
    }

    /**
     * @test
     */
    public function 対象データが0件だった場合終了する_処理状態が処理待ち以外のデータのみ()
    {
        //チャージ処理待ち以外のデータを作成
        $userNotReady = new CedynaPaymentFileWithoutReadyUserFixture();
        $userNotReady->load();

        $this->runBatch();

        //入金ファイル作成待ちのユーザーが更新されていないことを確認
        $chargeRequestHistory = ChargeRequestHistory::find()->where(['id' => 100001])->one();
        $this->assertEquals(ChargeRequestHistory::STATUS_IS_MAKING_PAYMENT_FILE,
            $chargeRequestHistory->processing_status);
    }

    /**
     * @test
     */
    public function 対象データが0件だった場合終了する_テーブルが空()
    {
        // チャージ履歴のデータが存在しない
        $userDataEmpty = new CedynaPaymentFileFixture();
        $userDataEmpty->loadEmptyUserData();

        $this->runBatch();

        $this->assertEquals(0, ChargeRequestHistory::find()->count());
    }

    /**
     * テストケース9 https://github.com/oz-sysb/schezzo/issues/61
     * @test
     */
    public function 入金ファイル作成中のデータ件数だけデータ行が出力される_データ1件()
    {
        $CedynaPaymentFileFixture = new CedynaPaymentFileFixture();
        $CedynaPaymentFileFixture->load();
        $expectedCount = 1;

        $this->runBatch();

        $writtenFile = CedynaFile::findAll($CedynaPaymentFileFixture->HULFT配信用ディレクトリ)[0];
        $actualCount = 0;
        foreach ($writtenFile->readDataLinesAll() as $line) {
            $actualCount++;
        }
        $this->assertEquals($expectedCount, $actualCount);
    }

    /**
     * テストケース10 https://github.com/oz-sysb/schezzo/issues/61
     * @test
     */
    public function 入金ファイル作成中のデータ件数だけデータ行が出力される_データ複数件()
    {
        $CedynaPaymentFileFixture = new CedynaPaymentFileFixture();
        $CedynaPaymentFileFixture->loadMultipleReadyUsers();
        $expectedCount = 2;

        $this->runBatch();

        $writtenFile = CedynaFile::findAll($CedynaPaymentFileFixture->HULFT配信用ディレクトリ)[0];
        $actualCount = 0;
        foreach ($writtenFile->readDataLinesAll() as $line) {
            $actualCount++;
        }
        $this->assertEquals($expectedCount, $actualCount);
    }

    /**
     * テストケース11 https://github.com/oz-sysb/schezzo/issues/61
     * @test
     */
    public function 処理したデータ行の数が終端行に出力される_データ1件()
    {
        $CedynaPaymentFileFixture = new CedynaPaymentFileFixture();
        $CedynaPaymentFileFixture->load();
        $expectedCount = 1;

        $this->runBatch();

        $writtenFile = CedynaFile::findAll($CedynaPaymentFileFixture->HULFT配信用ディレクトリ)[0];
        $lastLine = null;
        foreach ($writtenFile->readLinesAll() as $line) {
            if ($line[0] === 'E') {
                $lastLine = $line;
            }
        }
        // 終端行の2カラム目
        $actualCount = intval($lastLine[1]);
        $this->assertEquals($expectedCount, $actualCount);
    }

    /**
     * テストケース12 https://github.com/oz-sysb/schezzo/issues/61
     * @test
     */
    public function 処理したデータ行の数が終端行に出力される_データ複数件()
    {
        $CedynaPaymentFileFixture = new CedynaPaymentFileFixture();
        $CedynaPaymentFileFixture->loadMultipleReadyUsers();
        $expectedCount = 2;

        $this->runBatch();

        $writtenFile = CedynaFile::findAll($CedynaPaymentFileFixture->HULFT配信用ディレクトリ)[0];
        $lastLine = null;
        foreach ($writtenFile->readLinesAll() as $line) {
            if ($line[0] === 'E') {
                $lastLine = $line;
            }
        }
        // 終端行の2カラム目
        $actualCount = intval($lastLine[1]);
        $this->assertEquals($expectedCount, $actualCount);
    }

    /**
     * @test
     */
    public function 送信に成功したら処理したチャージ申請のステータスがチャージ申請済みに更新される()
    {
        $cedynaPaymentFixture = new CedynaPaymentFileFixture();
        $cedynaPaymentFixture->load();

        $this->runBatch();

        // もともと処理待ちのチャージ申請履歴
        $chargeRequestHistory = ChargeRequestHistory::find()->where(['id' => 100003])->one();
        $this->assertEquals(ChargeRequestHistory::STATUS_REQUESTED_CHARGE, $chargeRequestHistory->processing_status);
    }

    /**
     * @test
     */
    public function 送信に成功したら入金ファイル伝送管理状態が伝送中になる()
    {
        $cedynaPaymentFixture = new CedynaPaymentFileFixture();
        $cedynaPaymentFixture->load();

        $this->runBatch();

        // もともと伝送していない状態
        $paymentFileDeliveryManager = PaymentFileDeliveryManager::find()->one();
        // 伝送中に変わったことを確認
        $this->assertEquals(1, $paymentFileDeliveryManager->is_sending);
    }

    /**
     * @test
     */
    public function 送信に失敗したら処理したチャージ申請のステータスが処理待ちのままになる()
    {
        $cedynaPaymentFixture = new CedynaPaymentFileFixture();
        $cedynaPaymentFixture->load();

        // 失敗するモックで置き換える
        $this->setHulftMock(false);
        $this->runBatch();

        // もともと処理待ちのチャージ申請履歴
        $chargeRequestHistory = ChargeRequestHistory::find()->where(['id' => 100003])->one();
        $this->assertEquals(ChargeRequestHistory::STATUS_READY, $chargeRequestHistory->processing_status);
    }

    /**
     * @test
     */
    public function 送信に失敗したら入金ファイル伝送管理状態は伝送中じゃないまま変わらない()
    {
        $cedynaPaymentFixture = new CedynaPaymentFileFixture();
        $cedynaPaymentFixture->load();

        // 失敗するモックで置き換える
        $this->setHulftMock(false);
        $this->runBatch();

        // もともと伝送していない状態
        $paymentFileDeliveryManager = PaymentFileDeliveryManager::find()->one();
        // 伝送していない状態のまま変わっていないことを確認
        $this->assertEquals(0, $paymentFileDeliveryManager->is_sending);
    }

    /**
     * @test
     */
    public function 送信に失敗したら作成したファイルを削除する()
    {
        //処理待ちのユーザーのデータを用意
        $cedynaPaymentFixture = new CedynaPaymentFileFixture();
        $cedynaPaymentFixture->load();

        // 失敗するモックで置き換える
        $this->setHulftMock(false);
        $this->runBatch();

        $this->assertEmpty(CedynaFile::findAll($cedynaPaymentFixture->完了ディレクトリ));
        $this->assertEmpty(CedynaFile::findAll($cedynaPaymentFixture->HULFT配信用ディレクトリ));
    }

    /**
     * @test
     */
    public function 処理していないチャージ申請のステータスを更新しない()
    {
        $cedynaPaymentFixture = new CedynaPaymentFileFixture();
        $cedynaPaymentFixture->load();

        $this->runBatch();

        // もともと入金ファイル作成中のチャージ申請履歴
        $chargeRequestHistory = ChargeRequestHistory::find()->where(['id' => 100001])->one();
        $this->assertEquals(
            ChargeRequestHistory::STATUS_IS_MAKING_PAYMENT_FILE,
            $chargeRequestHistory->processing_status
        );
    }
}