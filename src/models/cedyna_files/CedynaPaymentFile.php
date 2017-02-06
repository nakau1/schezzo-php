<?php

namespace app\models\cedyna_files;

use app\models\ChargeRequestHistory;
use app\models\exceptions\FirstChargeRequest\ChargeNotFoundException;
use Yii;

class CedynaPaymentFile
{
    /**
     * 入金ファイル出力データを1行作成する
     *
     * @param ChargeRequestHistory $chargeRequestHistory
     * @return
     */
    public function outputRow(ChargeRequestHistory $chargeRequestHistory)
    {
        if (empty($chargeRequestHistory->id)) {
            throw new ChargeNotFoundException;
        }

        // レコード区分：固定値
        $outputRow[0] = '"D"';
        // 入金種別 : ポイント入金の固定値
        $outputRow[1] = '"0421"';
        // イシュアコード：固定値
        $outputRow[2] = '"CEDYNA"';
        // 提携先コード：固定値
        $outputRow[3] = '"0002"';
        // カード種別区分：提携先コード + 経路ID
        //                 経路ごとの振り分けの要件はないので固定値
        $outputRow[4] = '"00020001"';
        // 会員グループ番号
        $outputRow[5] = '"'.$chargeRequestHistory->polletUser->cedyna_id.'"';
        // 会員番号
        $outputRow[6] = '"'.$chargeRequestHistory->polletUser->cedyna_id.'"';
        // カードID
        $outputRow[7] = '""';
        // 入金額
        $outputRow[8] = '"'.$chargeRequestHistory->charge_value.'"';
        // 加盟店名（チャージ理由）
        $outputRow[9] = '"'.$chargeRequestHistory->cause.'"';
        // 処理結果
        $outputRow[10] = '""';
        // エラーコード
        $outputRow[11] = '""';
        // 処理番号
        $outputRow[12] = '"'.$chargeRequestHistory->id.'"';

        return $outputRow;
    }
}
