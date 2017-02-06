<?php
namespace app\commands;

use app\components\Hulft;
use app\helpers\Date;
use app\helpers\File;
use app\models\cedyna_files\CedynaFile;
use app\models\exceptions\Hulft\ReceivingException;
use Yii;

class ReceiveTradingHistoryFileController extends BatchController
{
    private $archiveFilesDirectory;

    public function init()
    {
        parent::init();

        $this->archiveFilesDirectory = Yii::$app->params['hulftPath'].'/app/receive_trading_history_file/archive';

        File::makeDirectoryIfNotExists($this->archiveFilesDirectory);
    }

    /**
     * セディナからHULFTで取引履歴情報ファイルを受け取り、アーカイブする。
     */
    public function actionIndex()
    {
        /** @var Hulft $hulft */
        $hulft = Yii::$app->get('hulft');
        try {
            $receivedFilePath = $hulft->receiveTradingHistoryFileSync();
            // もし失敗してたら例外が発生する
            Yii::info('取引履歴情報ファイルの集信に成功しました');
        } catch (ReceivingException $e) {
            // 失敗したらシステムでリカバーできないので、エラーログを出し、手動でデータの受取手配をする
            Yii::error('取引履歴情報ファイルの集信に失敗しました; exit status: '.$e->getCode().PHP_EOL.$e->getMessage());

            return;
        }

        $receivedFile = new CedynaFile($receivedFilePath);
        // 他のファイルと名前がかぶらないように日時をつける
        $receivedFile->renameTo((new Date())->format('YmdHis_').$receivedFile->getName());
        $receivedFile->moveTo($this->archiveFilesDirectory);
    }
}
