<?php
namespace app\models\cedyna_files;

use app\models\ChargeRequestHistory;
use app\models\exceptions\CedynaFile\CedynaPaymentDataException;
use RuntimeException;

class CedynaPaymentData
{
    const RESULT_CODE_SUCCESS = '0';
    const RESULT_CODE_ERROR = '1';

    const INDEX_VALUE = 8;
    const INDEX_RESULT_CODE = 10;
    const INDEX_ERROR_CODE = 11;
    const INDEX_CHARGE_REQUEST_HISTORY_ID = 12;

    public $value;
    public $result_code;
    public $error_code;
    public $charge_request_history_id;

    public function __construct(array $data)
    {
        $this->value                     = intval($data[self::INDEX_VALUE]);
        $this->result_code               = $data[self::INDEX_RESULT_CODE];
        $this->error_code                = $data[self::INDEX_ERROR_CODE];
        $this->charge_request_history_id = $data[self::INDEX_CHARGE_REQUEST_HISTORY_ID];

        $this->chargeRequestHistory = ChargeRequestHistory::find()->where([
            'id' => $this->charge_request_history_id,
        ])->one();
    }

    /**
     * @return bool
     */
    public function isSuccess()
    {
        switch ($this->result_code) {
            case self::RESULT_CODE_SUCCESS:
                return true;
            case self::RESULT_CODE_ERROR:
                return false;
            default:
                throw new RuntimeException('未定義の処理結果です');
        }
    }

    /**
     * @return CedynaPaymentDataException|null
     */
    public function getError()
    {
        switch ($this->error_code) {
            case '':
                // 正常
                return null;
            case '4150':
                return new CedynaPaymentDataException("[{$this->error_code}] カード状態不正（無効）");
            case '4151':
                return new CedynaPaymentDataException("[{$this->error_code}] カード有効期限切れ");
            case '4155':
                return new CedynaPaymentDataException("[{$this->error_code}] チャージ残高上限超過");
            case '9200':
                return new CedynaPaymentDataException("[{$this->error_code}] 設定値エラー");
            default:
                return new CedynaPaymentDataException("[{$this->error_code}] 未定義のエラー");
        }
    }
}