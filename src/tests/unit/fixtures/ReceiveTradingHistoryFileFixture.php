<?php
namespace tests\unit\fixtures;

use Faker;
use Yii;
use yii\helpers\FileHelper;

class ReceiveTradingHistoryFileFixture extends PolletDbFixture
{
    public $receivedFilesDirectory;
    public $archiveFilesDirectory;

    public $receivedFileName;
    public $receivedFilePath;

    public function init()
    {
        $this->receivedFilesDirectory = Yii::$app->params['hulftPath'].'/recv';
        $this->receivedFileName = 'scdpol03.txt';
        $this->receivedFilePath = "{$this->receivedFilesDirectory}/{$this->receivedFileName}";
        $this->archiveFilesDirectory = Yii::$app->params['hulftPath'].'/app/receive_trading_history_file/archive';
    }

    public function unload()
    {
        FileHelper::removeDirectory($this->receivedFilesDirectory);
        FileHelper::removeDirectory($this->archiveFilesDirectory);

        parent::unload();
    }

    public function load()
    {
        FileHelper::createDirectory($this->receivedFilesDirectory);
        FileHelper::createDirectory($this->archiveFilesDirectory);

        $this->makeReceivedCsv();
    }

    private function makeReceivedCsv()
    {
        $csv = <<<CSV
"履歴処理番号","処理日","処理種別","イシュアコード","提携先コード","カード種別区分","会員グループ番号","会員番号","カードID","取引日(現地)","加盟店コード","加盟店名称","処理額"
"000000000001","2016/01/01 13:00:00","0809","CEDYNA","dummy-code-12345","00011234","0000006460341068","0000006460341068","1234567890123456","2016/01/01 13:00:00","dummy-shop-1234","あじゃじゃ","1000.00"
CSV;
        file_put_contents($this->receivedFilePath, mb_convert_encoding($csv, 'SJIS'));
    }
}
