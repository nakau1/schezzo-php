<?php

namespace tests\unit\commands;

use app\components\Hulft;
use app\helpers\Date;
use app\models\cedyna_files\CedynaFile;
use app\models\exceptions\Hulft\ReceivingException;
use tests\unit\fixtures\ReceiveTradingHistoryFileFixture;
use Yii;
use yii\codeception\TestCase;

/**
 * Class ReceiveTradingHistoryFileControllerTest
 * @package tests\unit\commands
 * @property ReceiveTradingHistoryFileFixture $fixture
 */
class ReceiveTradingHistoryFileControllerTest extends TestCase
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
            'fixture' => ReceiveTradingHistoryFileFixture::class,
        ];
    }

    private function runBatch()
    {
        Yii::$app->runAction('receive-trading-history-file');
    }

    /**
     * バッチの中で HULFT を使ったら呼ばれるモックを設定する。
     *
     * @param bool $succeeds 送信が成功するかどうか
     */
    private function setHulftMock(bool $succeeds)
    {
        if (!$succeeds) {
            $mock = $this->createMock(Hulft::class);
            // 失敗したら例外が投げられるはず
            $mock->method('receiveTradingHistoryFileSync')
                ->willThrowException(new ReceivingException());
            Yii::$app->set('hulft', $mock);
        }
    }

    /**
     * @test
     */
    public function ファイルの処理が成功した場合処理完了ディレクトリに移動する()
    {
        $fileHash = md5_file("{$this->fixture->receivedFilesDirectory}/{$this->fixture->receivedFileName}");

        $this->runBatch();

        // ファイルを受け取るディレクトリからなくなってる
        $this->assertFileNotExists(
            "{$this->fixture->receivedFilesDirectory}/{$this->fixture->receivedFileName}"
        );

        // 処理完了ディレクトリに移動している
        $archivedFiles = CedynaFile::findAll($this->fixture->archiveFilesDirectory);
        $this->assertCount(1, $archivedFiles);
        $this->assertEquals($fileHash, md5_file($archivedFiles[0]->getPath()));
    }

    /**
     * @test
     */
    public function 処理完了ディレクトリに移動するときに既存のファイルを上書きしない()
    {
        $this->runBatch();

        // 次のバッチ実行
        Date::setTestNow((new Date())->addHour(1));
        $this->fixture->load();
        $this->runBatch();

        // 処理完了ディレクトリにファイルが2個ある＝前のファイルを上書きしてない
        $archivedFiles = CedynaFile::findAll($this->fixture->archiveFilesDirectory);
        $this->assertCount(2, $archivedFiles);

        // 時間を元に戻す
        Date::setTestNow((new Date())->addHour(-1));
    }
}