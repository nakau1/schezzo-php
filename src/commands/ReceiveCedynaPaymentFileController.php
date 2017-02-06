<?php
namespace app\commands;

use app\components\Hulft;
use app\helpers\Date;
use app\helpers\File;
use app\models\cedyna_files\CedynaFile;
use app\models\exceptions\Hulft\ReceivingException;
use app\models\PaymentFileDeliveryManager;
use app\models\ReceiveCedynaPaymentFile;
use Yii;
use yii\db\Exception;

class ReceiveCedynaPaymentFileController extends BatchController
{
    private $processingFilesDirectory;
    private $completeFilesDirectory;
    private $formatErrorFilesDirectory;

    public function init()
    {
        parent::init();

        $this->processingFilesDirectory = Yii::$app->params['hulftPath'].'/app/receive_payment_file/processing';
        $this->completeFilesDirectory = Yii::$app->params['hulftPath'].'/app/receive_payment_file/complete';
        $this->formatErrorFilesDirectory = Yii::$app->params['hulftPath'].'/app/receive_payment_file/format_error';

        File::makeDirectoryIfNotExists($this->processingFilesDirectory);
        File::makeDirectoryIfNotExists($this->completeFilesDirectory);
        File::makeDirectoryIfNotExists($this->formatErrorFilesDirectory);
    }

    /**
     * セディナからHULFTで入金結果ファイルを受け取り、以下の処理を行う。
     * - チャージ申請履歴のステータスを入金結果に応じて更新
     * - エラーの場合、
     *   + 提携サイトへ交換キャンセル申請
     *   + チャージ失敗履歴テーブルにレコードを残す
     *   + エラーログを残す
     */
    public function actionIndex()
    {
        /** 入金ファイルを伝送していないときは処理を実行しない
         * セディナさんの監視アラートがなり異常事態と認識するため **/
        if (!PaymentFileDeliveryManager::isSending()) {
            Yii::info('チャージ処理中の入金ファイルを生成していないので処理を終了します; ' . $this->getRoute());

            return;
        }

        /** @var Hulft $hulft */
        $hulft = Yii::$app->get('hulft');
        try {
            $receivedFilePath = $hulft->receiveCedynaPaymentFileSync();
            // もし失敗してたら例外が発生する
            Yii::info('入金結果ファイルの集信に成功しました; ' . $this->getRoute());
            // ファイル伝送管理を伝送中じゃない状態に戻す
            PaymentFileDeliveryManager::notSending();
        } catch (ReceivingException $e) {
            // FIXME: セディナ側にファイルが存在しなかった場合もエラーとしてしまう
            // 失敗したらシステムでリカバーできないので、エラーログを出し、手動でデータの受取手配をする
            Yii::error('入金結果ファイルの集信に失敗しました; exit status: ' . $e->getCode() . PHP_EOL . $e->getMessage() . '; ' . $this->getRoute());

            return;
        } catch (Exception $e){
            Yii::error('入金ファイルの伝送状態の更新に失敗しました。次回入金データ生成処理実行のためには伝送状態の更新が必要です; ' . $this->getRoute());
            // 次回の入金データの生成処理の開始には影響があるが、取得したファイルの処理は問題ないので続行
        }

        $receivedFile = new CedynaFile($receivedFilePath);
        // ファイルを多重処理しないように処理中ディレクトリに移動する
        $receivedFile->renameTo((new Date())->format('YmdHis_') . $receivedFile->getName());
        $receivedFile->moveTo($this->processingFilesDirectory);

        $receiveCedynaPaymentFile = new ReceiveCedynaPaymentFile(
            $this->completeFilesDirectory,
            $this->formatErrorFilesDirectory,
            $this->getRoute()
        );
        $receiveCedynaPaymentFile->acceptFile($receivedFile);
    }
}
