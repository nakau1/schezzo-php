<?php

namespace tests\unit\commands;

use app\components\HulftDummy;
use app\helpers\Date;
use app\models\cedyna_files\CedynaFile;
use app\models\ChargeRequestHistory;
use app\models\exceptions\Hulft\ReceivingException;
use app\models\PolletUser;
use tests\unit\fixtures\ReceiveNumberedCedynaIdFileFixture;
use tests\unit\fixtures\ReceiveNumberedCedynaIdFixture;
use Yii;
use yii\codeception\TestCase;
use yii\helpers\FileHelper;

/**
 * Class ReceiveNumberedCedynaIdControllerTest
 * @package tests\unit\commands
 * @property ReceiveNumberedCedynaIdFixture $fixture
 * @property ReceiveNumberedCedynaIdFileFixture $fileFixture
 */
class ReceiveNumberedCedynaIdControllerTest extends TestCase
{
    public $appConfig = '@app/config/console.php';
    private $savedDb;

    public function setUp()
    {
        parent::setUp();

        $this->savedDb = Yii::$app->getDb();
        $this->setHulftMock(true);
    }

    public function fixtures()
    {
        return [
            'fixture'     => ReceiveNumberedCedynaIdFixture::class,
            'fileFixture' => ReceiveNumberedCedynaIdFileFixture::class,
        ];
    }

    private function runBatch()
    {
        Yii::$app->runAction('receive-numbered-cedyna-id');
    }

    private function detachDb()
    {
        Yii::$app->set('db', [
            'class'    => 'yii\db\Connection',
            'dsn'      => 'invalid',
            'username' => 'invalid',
            'password' => 'invalid',
        ]);
    }

    private function attachDb()
    {
        Yii::$app->set('db', $this->savedDb);
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
            $mock->method('receiveNumberedCedynaIdFileSync')
                ->willThrowException(new ReceivingException());
            Yii::$app->set('hulft', $mock);
        }
    }


    /**
     * @test
     */
    public function ユーザー情報の登録状態がカード未認証状態へ更新される()
    {
        $this->runBatch();
        /** @var PolletUser $user */
        $user = PolletUser::findOne($this->fileFixture->集信したpolletId[0]);
        $this->assertEquals(PolletUser::STATUS_ISSUED, $user->registration_status);
    }

    /**
     * @test
     * @dataProvider 処理対象のセディナIDとpolletId
     *
     * @param string $expectedCedynaId
     * @param int $polletId
     */
    public function ユーザー情報にセディナIDが追加される(string $expectedCedynaId, int $polletId)
    {
        $this->runBatch();

        /** @var PolletUser $user */
        $user = PolletUser::findOne($polletId);
        $this->assertEquals($expectedCedynaId, $user->cedyna_id);
    }

    public function 処理対象のセディナIDとpolletId()
    {
        return [
            // それぞれ2行目以降も処理できることを確認する
            '集信したファイルの1行目'   => [
                $this->fileFixture->集信したセディナID[0],
                $this->fileFixture->集信したpolletId[0],
            ],
            '集信したファイルの2行目'   => [
                $this->fileFixture->集信したセディナID[1],
                $this->fileFixture->集信したpolletId[1],
            ],
            'リトライするファイルの1行目' => [
                $this->fileFixture->リトライするセディナID[0],
                $this->fileFixture->リトライするpolletId[0],
            ],
            'リトライするファイルの2行目' => [
                $this->fileFixture->リトライするセディナID[1],
                $this->fileFixture->リトライするpolletId[1],
            ],
        ];
    }

    /**
     * @test
     */
    public function チャージ申請履歴の処理状態が処理待ちへ更新される()
    {
        $this->runBatch();
        /** @var PolletUser $user */
        $user = PolletUser::findOne($this->fileFixture->集信したpolletId[0]);
        $this->assertEquals(
            ChargeRequestHistory::STATUS_READY,
            $user->chargeRequestHistories[0]->processing_status
        );
    }

    /**
     * @test
     */
    public function 集信ディレクトリから処理したファイルが処理済みディレクトリに移動している()
    {
        $originalContent = file_get_contents($this->fileFixture->receivedFilePath);

        $this->runBatch();

        // 処理済みディレクトリにファイルが来ている
        $moved = false;
        foreach (CedynaFile::findAll($this->fileFixture->completeDirectory) as $file) {
            if (file_get_contents($file->getPath()) === $originalContent) {
                $moved = true;
            }
        }
        $this->assertTrue($moved);

        // 元のディレクトリからファイルがなくなってる
        $this->assertFileNotExists($this->fileFixture->receivedFilePath);
    }

    /**
     * @test
     */
    public function リトライディレクトリから処理したファイルが処理済みディレクトリに移動している()
    {
        $originalContent = file_get_contents($this->fileFixture->retryFilePath);

        $this->runBatch();

        // 処理済みディレクトリにファイルが来ている
        $moved = false;
        foreach (CedynaFile::findAll($this->fileFixture->completeDirectory) as $file) {
            if (file_get_contents($file->getPath()) === $originalContent) {
                $moved = true;
            }
        }
        $this->assertTrue($moved);

        // 元のディレクトリからファイルがなくなってる
        $this->assertFileNotExists($this->fileFixture->retryFilePath);
    }

    /**
     * @test
     */
    public function 処理完了ディレクトリに移動するときに既存のファイルを上書きしない()
    {
        $this->runBatch();
        $fileCountBeforeRunBatch = count(CedynaFile::findAll($this->fileFixture->completeDirectory));

        // 次のバッチ実行
        Date::setTestNow((new Date())->addHour(1));
        $this->fileFixture->makeCsvEmpty();
        $this->runBatch();

        // 処理完了ディレクトリにファイルが1個増えてる＝前のファイルを上書きしてない
        $archivedFiles = CedynaFile::findAll($this->fileFixture->completeDirectory);
        $this->assertCount($fileCountBeforeRunBatch + 1, $archivedFiles);

        // 時間を元に戻す
        Date::setTestNow((new Date())->addHour(-1));
    }

    /**
     * @test
     * @dataProvider 無効なセディナIDを持つpolletId
     *
     * @param string $polletId
     */
    public function セディナIDが無効の場合ユーザー情報もチャージ申請履歴も更新しない(string $polletId)
    {
        $this->fileFixture->makeCsvInvalidCedynaIds();
        $this->runBatch();

        /** @var PolletUser $user */
        $user = PolletUser::findOne($polletId);
        $this->assertEquals(PolletUser::STATUS_WAITING_ISSUE, $user->registration_status);
        $this->assertEquals(
            ChargeRequestHistory::STATUS_UNPROCESSED_FIRST_CHARGE,
            $user->chargeRequestHistories[0]->processing_status
        );
    }

    public function 無効なセディナIDを持つpolletId()
    {
        return [
            '数字以外を含む'  => [$this->fileFixture->セディナIDに数字以外を含むpolletId],
            '0より小さい'   => [$this->fileFixture->セディナIDが0より小さいpolletId],
            '16桁より大きい' => [$this->fileFixture->セディナIDが16桁より大きいpolletId],
        ];
    }

    /**
     * @test
     */
    public function セディナIDが重複している場合ユーザー情報もチャージ申請履歴も更新しない()
    {
        $this->fileFixture->makeCsvDuplicatedCedynaIds();
        $this->runBatch();

        /** @var PolletUser $user */
        $user = PolletUser::findOne($this->fileFixture->重複するセディナIDを持つpolletId);
        $this->assertEquals(PolletUser::STATUS_WAITING_ISSUE, $user->registration_status);
        $this->assertEquals(
            ChargeRequestHistory::STATUS_UNPROCESSED_FIRST_CHARGE,
            $user->chargeRequestHistories[0]->processing_status
        );
    }

    /**
     * @test
     */
    public function DBの異常で処理できなかった行がある場合リトライディレクトリにファイルを出力する()
    {
        $retryFilesCountBeforeRun = count(CedynaFile::findAll($this->fileFixture->retryDirectory));

        $this->detachDb();
        $this->runbatch();

        // リトライディレクトリにファイルがふえてる
        $retryFilesCount = count(CedynaFile::findAll($this->fileFixture->retryDirectory));
        $this->assertEquals($retryFilesCountBeforeRun + 1, $retryFilesCount);

        // 元のディレクトリからファイルがなくなってる
        $this->assertFileNotExists("{$this->fileFixture->receivedFileName}/{$this->fileFixture->receivedFileName}");
    }

    /**
     * @test
     */
    public function リトライディレクトリに出力したファイルを再度処理することができる()
    {
        $this->detachDb();
        $this->runbatch();

        $this->attachDb();
        $this->runbatch();

        /** @var PolletUser $user */
        $user = PolletUser::findOne($this->fileFixture->集信したpolletId[0]);
        $this->assertEquals($this->fileFixture->集信したセディナID[0], $user->cedyna_id);
        $this->assertEquals(
            ChargeRequestHistory::STATUS_READY,
            $user->chargeRequestHistories[0]->processing_status
        );
    }

    /**
     * @test
     */
    public function ファイルの読み取り時にデータ行以外を処理しない()
    {
        $this->fileFixture->makeCsvIncludesInvalidLines();
        $this->runBatch();

        // 処理しない
        /** @var PolletUser $user */
        $user = PolletUser::findOne($this->fileFixture->ヘッダ行に存在するpolletId);
        $this->assertEquals(PolletUser::STATUS_WAITING_ISSUE, $user->registration_status);

        // 処理しない
        /** @var PolletUser $user */
        $user = PolletUser::findOne($this->fileFixture->トレーラ行に存在するpolletId);
        $this->assertEquals(PolletUser::STATUS_WAITING_ISSUE, $user->registration_status);

        // 処理する
        /** @var PolletUser $user */
        $user = PolletUser::findOne($this->fileFixture->データ行に存在するpolletId);
        $this->assertEquals(PolletUser::STATUS_ISSUED, $user->registration_status);
    }

    /**
     * @test
     */
    public function 処理に必要なディレクトリが存在しない場合作成した上で処理する()
    {
        FileHelper::removeDirectory($this->fileFixture->retryDirectory);
        FileHelper::removeDirectory($this->fileFixture->processingDirectory);
        FileHelper::removeDirectory($this->fileFixture->completeDirectory);

        $this->runBatch();

        $this->assertFileExists($this->fileFixture->retryDirectory);
        $this->assertFileExists($this->fileFixture->processingDirectory);
        $this->assertFileExists($this->fileFixture->completeDirectory);
    }
}