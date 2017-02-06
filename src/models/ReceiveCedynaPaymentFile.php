<?php

namespace app\models;

use app\components\CedynaMyPageWithCache;
use app\components\PushNotify;
use app\helpers\Format;
use app\models\cedyna_files\CedynaFile;
use app\models\cedyna_files\CedynaPaymentData;
use app\models\cedyna_files\CedynaPaymentFileHeader;
use app\models\charge_source_cooperation\ChargeSourceCooperation;
use app\models\exceptions\ChargeSourceCooperation\CancelWithdrawalFailedException;
use app\models\exceptions\ReceiveCedynaPaymentFile\ChargeRequestNotFoundException;
use Exception;
use Yii;
use yii\base\Model;

class ReceiveCedynaPaymentFile extends Model
{
    /** @var string */
    private $completeDirectory;
    /** @var string */
    private $formatErrorDirectory;
    /** @var string */
    private $batchName;

    /**
     * ReceiveCedynaPaymentFile constructor.
     * @param string $completeDirectory 処理が完了したファイルを入れる
     * @param string $formatErrorDirectory フォーマットのエラーだったファイルを入れる
     * @param string $batchName ログに出力するためのバッチ名
     * @param array $config
     */
    public function __construct(
        string $completeDirectory,
        string $formatErrorDirectory,
        string $batchName = '',
        $config = []
    ) {
        parent::__construct($config);

        $this->completeDirectory = $completeDirectory;
        $this->formatErrorDirectory = $formatErrorDirectory;
        $this->batchName = $batchName;
    }

    /**
     * 1つの入金結果ファイルについて、以下の処理を行う。
     * - チャージ申請履歴のステータスを入金結果に応じて更新
     * - エラーの場合、
     *   + 提携サイトへ交換キャンセル申請
     *   + チャージ失敗履歴テーブルにレコードを残す
     *   + エラーログを残す
     *
     * @param CedynaFile $file
     * @throws Exception
     */
    public function acceptFile(CedynaFile $file)
    {
        Yii::info("{$this->batchName}: begin processing file: {$file->getPath()}");

        // フォーマットのエラーを検出
        $header = new CedynaPaymentFileHeader($file->readHeaderLine());
        $formatError = $header->getFormatError();
        if (!empty($formatError)) {
            $file->moveTo($this->formatErrorDirectory);
            Yii::error("{$this->batchName}: file format error: {$formatError->getMessage()}; {$file->getPath()}");

            return;
        }

        foreach ($file->readDataLinesAll() as $row) {
            // TODO: エンコードはCedynaFileの中でやりたい
            $row = array_map(function (string $column) {
                return mb_convert_encoding($column, 'UTF8', 'SJIS');
            }, $row);

            try {
                $this->acceptRow($row, $file);
            } catch (ChargeRequestNotFoundException $e) {
                Yii::error("{$this->batchName}: {$e->getMessage()}".PHP_EOL.$this->rowArrayToString($row));
                continue;
            } catch (Exception $e) {
                // 不明な例外（DBに繋がらなかったなど）
                Yii::error("{$this->batchName}: {$e->getMessage()}".PHP_EOL.$this->rowArrayToString($row));
                throw $e;
            }

            $payment = new CedynaPaymentData($row);
            $pushSuccess = $this->pushNotifyChargeResult($payment);
            if (!$pushSuccess) {
                Yii::warning("{$this->batchName}: プッシュ通知に失敗しました; charge_request_id={$payment->charge_request_history_id}; {$file->getPath()}");
            }
        }

        $file->moveTo($this->completeDirectory);
        Yii::info("{$this->batchName}: finish processing file: {$file->getPath()}");
    }

    /**
     * データ行1つを処理する
     *
     * @param array $row
     * @param CedynaFile $file
     */
    private function acceptRow(array $row, CedynaFile $file)
    {
        $payment = new CedynaPaymentData($row);

        $chargeRequest = $payment->chargeRequestHistory;
        if (empty($chargeRequest)) {
            throw new ChargeRequestNotFoundException("チャージ申請履歴が存在しません; id={$payment->charge_request_history_id}; {$file->getPath()}");
        }

        if ($payment->isSuccess()) {
            $chargeRequest->processing_status = ChargeRequestHistory::STATUS_APPLIED_CHARGE;
            $chargeRequest->save();

            $this->updateReceptionStatusOfChargeRequestHistory($chargeRequest, Reception::RECEPTION_STATUS_COMPLETED);

            $cardValue = 0;
            try {
                $user = $chargeRequest->polletUser;
                if ($user->cedyna_id && $user->rawPassword) {
                    $myPage = CedynaMyPageWithCache::getInstance();
                    $myPage->login($user->cedyna_id, $user->rawPassword);
                    $cardValue = $myPage->cardValue();
                }
            } catch (\Exception $e) {
                Yii::warning("{$this->batchName}: チャージ残高取得のためのログインに失敗しました; user={$chargeRequest->polletUser->id}");
            }
            $chargeRequest->polletUser->balance_at_charge = $cardValue;
            $chargeRequest->polletUser->save();

        } else {
            $chargeRequest->processing_status = ChargeRequestHistory::STATUS_ERROR;
            $chargeRequest->save();

            $this->updateReceptionStatusOfChargeRequestHistory($chargeRequest, Reception::RECEPTION_STATUS_ERROR);

            try {
                ChargeSourceCooperation::cancelWithdrawal(
                    $chargeRequest->chargeSource,
                    $chargeRequest->id
                );
            } catch (CancelWithdrawalFailedException $e) {
                Yii::error("{$this->batchName}: 引き落としのキャンセル処理に失敗しました; charge_request_id={$chargeRequest->id}; {$e->getMessage()}; {$file->getPath()}");
            }

            $errorHistory = new ChargeErrorHistory();
            $errorHistory->charge_request_history_id = $chargeRequest->id;
            $errorHistory->error_code = $payment->error_code;
            $errorHistory->raw_data = $this->rowArrayToString($row);
            $errorHistory->save();

            $error = $payment->getError();
            Yii::warning("{$this->batchName}: {$error->getMessage()}; id={$chargeRequest->id}; {$file->getPath()}".PHP_EOL.$this->rowArrayToString($row));
        }
    }

    /**
     * 行の配列を文字列に直す
     *
     * @param array $row
     * @return string
     */
    private function rowArrayToString(array $row)
    {
        // FIXME: エスケープしてないためカンマや"があると正しくなくなる
        return '"'.implode('","', $row).'"';
    }

    /**
     * チャージ結果をプッシュ通知する
     *
     * @param CedynaPaymentData $payment
     * @return bool 成否
     */
    private function pushNotifyChargeResult(CedynaPaymentData $payment): bool
    {
        $user = $payment->chargeRequestHistory->polletUser;
        $badgeCount = $user->unreadInformationCount + 1; // 未読お知らせ数 + チャージ成功/失敗通知(1件)

        if ($payment->isSuccess()) {
            $message = Format::formattedNumber($payment->value) . '円チャージされました。';
            $uri = 'top';
            $confirmText = PushNotify::CONFIRM_TEXT_TOP;
        } else {
            $message = 'チャージに失敗しました。';
            $uri = 'inquiry';
            $confirmText = PushNotify::CONFIRM_TEXT_INQUIRY;
        }

        $pushNotify = new PushNotify();
        return $pushNotify->sendToUser($user, $message, $uri, $badgeCount, $confirmText);
    }

    /**
     * チャージ申請履歴に紐付く受付のステータスを更新
     * @param ChargeRequestHistory $chargeRequest チャージ申請履歴モデル
     * @param string $status ステータス
     */
    private function updateReceptionStatusOfChargeRequestHistory($chargeRequest, $status)
    {
        $reception = $chargeRequest->reception;
        if ($reception) {
            $reception->reception_status = $status;
            $reception->save();
        }
    }
}
