<?php
namespace app\models\cedyna_files;

use app\models\exceptions\CedynaFile\CedynaPaymentFileFormatException;

class CedynaPaymentFileHeader
{
    public $error_code;

    public function __construct(array $row)
    {
        $this->error_code = $row[13];
    }

    /**
     * @return CedynaPaymentFileFormatException|null
     */
    public function getFormatError()
    {
        switch ($this->error_code) {
            case '':
                // 正常
                return null;
            case 'A002':
                return new CedynaPaymentFileFormatException("[{$this->error_code}] 開始行エラー");
            case 'A003':
                return new CedynaPaymentFileFormatException("[{$this->error_code}] ヘッダ行エラー");
            case 'A004':
                return new CedynaPaymentFileFormatException("[{$this->error_code}] データ行エラー");
            case 'A005':
                return new CedynaPaymentFileFormatException("[{$this->error_code}] 終端行エラー");
            case 'A006':
                return new CedynaPaymentFileFormatException("[{$this->error_code}] レコード区分不正");
            case 'A007':
                return new CedynaPaymentFileFormatException("[{$this->error_code}] 開始行が1行目のレコードではない");
            case 'A008':
                return new CedynaPaymentFileFormatException("[{$this->error_code}] ヘッダ行が2行目のレコードではない");
            case 'A009':
                return new CedynaPaymentFileFormatException("[{$this->error_code}] 項目数エラー");
            case 'A010':
                return new CedynaPaymentFileFormatException("[{$this->error_code}] 件数エラー");
            case 'A011':
                return new CedynaPaymentFileFormatException("[{$this->error_code}] 終端行エラー");
            default:
                return new CedynaPaymentFileFormatException("[{$this->error_code}] 未定義のエラー");
        }
    }
}