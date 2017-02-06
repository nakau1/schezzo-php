<?php
namespace app\commands;

use app\helpers\File;
use app\models\BatchManagement;
use app\models\cedyna_files\CedynaFile;
use app\models\PaymentFileDeliveryManager;
use app\models\SendCedynaPaymentFile;
use Yii;
use yii\db\Exception;

class SendCedynaPaymentFileController extends BatchController
{
    private $sendFilesDirectory;
    private $processingFilesDirectory;
    private $completeFilesDirectory;

    public function init()
    {
        parent::init();

        $this->sendFilesDirectory = Yii::$app->params['hulftPath'].'/send';
        $this->processingFilesDirectory = Yii::$app->params['hulftPath'].'/app/send_payment_file/processing';
        $this->completeFilesDirectory = Yii::$app->params['hulftPath'].'/app/send_payment_file/complete';

        File::makeDirectoryIfNotExists($this->sendFilesDirectory);
        File::makeDirectoryIfNotExists($this->processingFilesDirectory);
        File::makeDirectoryIfNotExists($this->completeFilesDirectory);
    }

    /**
     * チャージ申請履歴から入金ファイルを作成
     */
    public function actionIndex()
    {
        // ファイル伝送状態が伝送中の場合は前回の処理が結果取得まで完了していないので終わるまで実行しない
        if (PaymentFileDeliveryManager::isSending()) {
            Yii::warning('前回実行分の結果取得が完了していないのでバッチを実行をとりやめました; ' . $this->getRoute());

            return;
        }
        // ファイル配信ディレクトリにファイルが存在する＝前回のファイルが送信されていない
        $files = CedynaFile::findAll($this->sendFilesDirectory);
        if (!empty($files)) {
            Yii::warning('既にファイル出力先にファイルが存在します; '.$this->getRoute());

            return;
        }

        // 多重起動防止
        if (BatchManagement::isActive($this->getRoute())) {
            Yii::warning('During start-up in the other process : '.$this->getRoute());

            return;
        }
        BatchManagement::activate($this->getRoute());

        $sendCedynaPaymentFile = new SendCedynaPaymentFile(
            $this->processingFilesDirectory,
            $this->completeFilesDirectory
        );
        try {
            $sendCedynaPaymentFile->run($this->getRoute());
        } catch (Exception $e) {
            Yii::error("データベースエラーが発生したため処理を中断しました。{$e->getMessage()} {$this->getRoute()}");

            // 次回変な状態でバッチが起動しないようにこのまま処理を終了する
            return;
        }

        // 多重起動防止のため終了記録を更新
        BatchManagement::inactivate($this->getRoute());
    }
}
