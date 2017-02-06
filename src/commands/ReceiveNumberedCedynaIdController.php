<?php
namespace app\commands;

use app\components\Hulft;
use app\helpers\Date;
use app\helpers\File;
use app\models\cedyna_files\CedynaFile;
use app\models\exceptions\Hulft\ReceivingException;
use app\models\ReceiveNumberedCedynaId;
use Yii;

class ReceiveNumberedCedynaIdController extends BatchController
{
    private $retryDirectory;
    private $processingDirectory;
    private $completeDirectory;

    public function init()
    {
        parent::init();

        $this->retryDirectory = Yii::$app->params['hulftPath'].'/app/receive_numbered_cedyna_id/retry';
        $this->processingDirectory = Yii::$app->params['hulftPath'].'/app/receive_numbered_cedyna_id/processing';
        $this->completeDirectory = Yii::$app->params['hulftPath'].'/app/receive_numbered_cedyna_id/complete';

        File::makeDirectoryIfNotExists($this->retryDirectory);
        File::makeDirectoryIfNotExists($this->processingDirectory);
        File::makeDirectoryIfNotExists($this->completeDirectory);
    }

    /**
     * セディナからHULFTでセディナID発番通知ファイルを受け取り、以下の処理を行う。
     * - 対象のユーザー情報にセディナIDを追加
     * - 対象のユーザー情報の登録状態を「発番済」に更新
     * - 対象のチャージ申請データの処理状態を「処理待ち」に更新
     */
    public function actionIndex()
    {
        /** @var Hulft $hulft */
        $hulft = Yii::$app->get('hulft');
        try {
            $receivedFilePath = $hulft->receiveNumberedCedynaIdFileSync();
            // もし失敗してたら例外が発生する
            Yii::info('セディナID発番通知ファイルの集信に成功しました');
        } catch (ReceivingException $e) {
            // 失敗したらシステムでリカバーできないので、エラーログを出し、手動でデータの受取手配をする
            Yii::error('セディナID発番通知ファイルの集信に失敗しました; exit status: '.$e->getCode().PHP_EOL.$e->getMessage());
            return;
        }

        /** @var CedynaFile[] $files */
        $files = CedynaFile::findAll($this->retryDirectory);

        if (file_exists($receivedFilePath)) {
            $receivedFile = new CedynaFile($receivedFilePath);
            // リトライするファイルと名前がかぶらないように日時をつける
            $receivedFile->renameTo((new Date())->format('YmdHis_').$receivedFile->getName());
            $files = array_merge($files, [$receivedFile]);
        }

        // 1つのファイルを多重処理しないようにすべて処理中ディレクトリに移動する
        foreach ($files as $file) {
            $file->moveTo($this->processingDirectory);
        }

        $model = new ReceiveNumberedCedynaId($this->completeDirectory, $this->retryDirectory);
        foreach ($files as $file) {
            $model->acceptFile($file);
        }
    }
}
