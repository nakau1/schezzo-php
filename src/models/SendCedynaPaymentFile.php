<?php
namespace app\models;

use app\components\Hulft;
use app\models\cedyna_files\CedynaFile;
use app\models\cedyna_files\CedynaPaymentFile;
use app\models\exceptions\CedynaFile\DirectoryNotWritableException;
use app\models\exceptions\CedynaFile\FileAlreadyExistsException;
use app\models\exceptions\CedynaFile\FileWritingFailedException;
use app\models\exceptions\Hulft\SendingException;
use Yii;
use yii\base\Model;

class SendCedynaPaymentFile extends Model
{
    /** @var string */
    private $processingDirectory;
    /** @var string */
    private $archiveCompleteDirectory;
    /** @var Hulft */
    private $hulft;

    public function init()
    {
        parent::init();

        $this->hulft = Yii::$app->get('hulft');
    }

    /**
     * SendCedynaPaymentFile constructor.
     *
     * @param string $processingDirectory 処理中のファイルを入れておくディレクトリ
     * @param string $archiveCompleteDirectory 処理が完了したファイルをとっておくディレクトリ
     * @param array $config
     */
    public function __construct(
        string $processingDirectory,
        string $archiveCompleteDirectory,
        $config = []
    ) {
        parent::__construct($config);

        $this->processingDirectory = $processingDirectory;
        $this->archiveCompleteDirectory = $archiveCompleteDirectory;
    }

    /**
     * チャージ申請履歴から入金ファイルを作成
     *
     * @param string $batchName
     */
    public function run(string $batchName)
    {
        Yii::info('begin processing make payment file: '.$batchName);

        $targetCharges = $this->findReadyCharges();
        if (count($targetCharges) === 0) {
            Yii::info('data does not exist : '.$batchName);

            return;
        }
        // 途中で中断しても対象データを引っ張れるように、いったん作成中にする
        $this->updateChargesTo(ChargeRequestHistory::STATUS_IS_MAKING_PAYMENT_FILE, $targetCharges);

        $paymentFile = $this->createPaymentFileInto($this->processingDirectory, $targetCharges);
        if ($paymentFile === null) {
            Yii::error('failure to file creation : '.$batchName);
            // 次回のバッチ実行時に拾えるように処理待ち状態に戻す
            $this->updateChargesTo(ChargeRequestHistory::STATUS_READY, $targetCharges);

            return;
        }

        Yii::info('end processing make payment file: '.$batchName);

        // 入金ファイルに出力したチャージ申請に絞る
        $outputCharges = array_filter($targetCharges, function (ChargeRequestHistory $charge) {
            return $charge->processing_status === ChargeRequestHistory::STATUS_MADE_PAYMENT_FILE;
        });
        try {
            $this->hulft->sendCedynaPaymentFileSync($paymentFile);
            // もし失敗してたら例外が発生する
            Yii::info('入金ファイルの送信に成功しました');
            $this->updateChargesTo(ChargeRequestHistory::STATUS_REQUESTED_CHARGE, $outputCharges);
            // 入金ファイル伝送状態管理を伝送中にする
            PaymentFileDeliveryManager::sending();
            $succeeded = true;
        } catch (SendingException $e) {
            Yii::error('入金ファイルの送信に失敗しました; exit status: '.$e->getCode().PHP_EOL.$e->getMessage());
            $succeeded = false;
        } catch (DirectoryNotWritableException $e) {
            Yii::error($e->getMessage());
            $succeeded = false;
        } catch (FileAlreadyExistsException $e) {
            Yii::error($e->getMessage().'; 前回出力のファイルが送信されていません');
            $succeeded = false;
        } catch (FileWritingFailedException $e) {
            Yii::error($e->getMessage());
            $succeeded = false;
        }

        if ($succeeded) {
            $paymentFile->moveTo($this->archiveCompleteDirectory);
        } else {
            // 次回のバッチ実行時に拾えるように処理待ち状態に戻す
            $this->updateChargesTo(ChargeRequestHistory::STATUS_READY, $outputCharges);
            // セディナ側にファイルが存在した場合ファイルが残るため削除する
            $paymentFile->remove();
        }
    }

    /**
     * 指定したディレクトリに入金ファイルを作成する
     *
     * @param string $directory
     * @param array $charges
     * @return CedynaFile|null 作成したファイル。失敗した場合null
     */
    private function createPaymentFileInto(string $directory, array $charges)
    {
        $now = date('YmdHis');
        if (false === touch("{$directory}/{$now}.csv")) {
            return null;
        }

        $paymentFile = new CedynaFile("{$directory}/{$now}.csv");
        $this->outputFirstRow($now, $paymentFile);
        $this->outputHeader($paymentFile);
        $this->outputData($paymentFile, $charges);
        $this->outputLastRow($paymentFile);

        return $paymentFile;
    }

    /**
     * 処理待ち状態のチャージ申請履歴レコード
     *
     * @return \app\models\ChargeRequestHistory[]|array
     */
    private function findReadyCharges()
    {
        return ChargeRequestHistory::find()->where(
            ['processing_status' => ChargeRequestHistory::STATUS_READY]
        )->all();
    }

    /**
     * 指定したチャージ申請履歴の処理状態をすべて更新する
     *
     * @param string $processingStatus
     * @param ChargeRequestHistory[] $charges
     */
    private function updateChargesTo(string $processingStatus, array $charges)
    {
        $ids = [];
        foreach ($charges as $charge) {
            $ids[] = $charge->id;
        }

        ChargeRequestHistory::updateAll(
            ['processing_status' => $processingStatus],
            ['in', 'id', $ids]
        );
    }

    /**
     * データ行の行数
     *
     * @param CedynaFile $file
     * @return int
     */
    private function countDataRow(CedynaFile $file)
    {
        $count = 0;
        foreach ($file->readDataLinesAll() as $line) {
            $count = ++$count;
        }

        return $count;
    }

    /**
     * 開始行を出力
     *
     * @param string $now
     * @param CedynaFile $paymentFile
     */
    private function outputFirstRow(string $now, CedynaFile $paymentFile)
    {
        $startRowDate = date('Y/m/d H:i:s', strtotime($now));
        $content = '"S",'.'"'.$startRowDate.'"'."\n";
        $paymentFile->setSaveContent($content);
        $paymentFile->save();
    }

    /**
     * ヘッダ行を出力
     * 入金結果ファイル＞ヘッダー行
     * 以下3項目をカンマ区切りにする
     * - レコード区分
     * - 項目名
     * - 処理結果
     * https://drive.google.com/drive/u/1/folders/0BxxKoWe0vrvOYm5rbzdyNDlpU0k
     *
     * @param CedynaFile $paymentFile
     */
    private function outputHeader(CedynaFile $paymentFile)
    {
        $content = '"H","入金種別","イシュアコード","提携先コード","カード種別区分","会員グループ番号","会員番号","カードID","入金額","加盟店名（チャージ理由）","処理結果","エラーコード","処理番号",""' . "\n";
        $paymentFile->setSaveContent($content);
        $paymentFile->save(true);
    }

    /**
     * データ行を出力
     *
     * @param CedynaFile $paymentFile
     * @param ChargeRequestHistory[] $chargeRequests
     */
    private function outputData(CedynaFile $paymentFile, array $chargeRequests)
    {
        foreach ($chargeRequests as $chargeRequest) {
            Yii::info("begin output to file : id : {$chargeRequest->id}");

            $cedynaPaymentFile = new CedynaPaymentFile();

            $content = implode(',', $cedynaPaymentFile->outputRow($chargeRequest))."\n";
            $paymentFile->setSaveContent($content);
            try {
                $paymentFile->save(true);
                $chargeRequest->processing_status = ChargeRequestHistory::STATUS_MADE_PAYMENT_FILE;
                $chargeRequest->save();
            } catch (FileWritingFailedException $e) {
                Yii::error($e->getMessage());
                // 次回のバッチ実行時に拾えるように処理待ち状態に戻す
                $chargeRequest->processing_status = ChargeRequestHistory::STATUS_READY;
                $chargeRequest->save();
            }

            Yii::info("finish output to file : id : {$chargeRequest->id}");
        }
    }

    /**
     * 終端行を出力
     *
     * @param CedynaFile $paymentFile
     */
    private function outputLastRow(CedynaFile $paymentFile)
    {
        $content = '"E","'.$this->countDataRow($paymentFile).'"'."\n";
        $paymentFile->setSaveContent($content);
        $paymentFile->save(true);
    }
}
