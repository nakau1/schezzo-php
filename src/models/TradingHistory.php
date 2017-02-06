<?php

namespace app\models;

use Carbon\Carbon;
use DomainException;
use InvalidArgumentException;
use yii\base\Object;

/**
 * 取引履歴
 * @package app\models
 */
class TradingHistory extends Object
{
    /** 取引種別の表示名 */
    const TYPE_USE = '決済';
    const TYPE_CHARGE = 'チャージ';
    const TYPE_CHARGE_FEE = '手数料';

    /** @var string */
    public $shop;
    /** @var int */
    public $spentValue;
    /** @var Carbon */
    public $tradingDate;
    /** @var string */
    public $tradingType;

    /**
     * @param array $data
     * @return static
     */
    public static function createFromArray(array $data)
    {
        $result = new static();
        $result->shop         = $data['shop'];
        $result->spentValue   = $data['spent_value'];
        $result->tradingDate  = Carbon::parse($data['trading_date']);
        $result->tradingType  = $data['trading_type'];

        return $result;
    }

    /**
     * ChargeRequestHistoryの配列からTradingHistoryの配列を生成する
     * @param ChargeRequestHistory[] $chargeRequestHistories チャージ履歴の配列
     * @return static[] 取引履歴の配列
     */
    public static function createFromChargeRequestHistories($chargeRequestHistories)
    {
        $ret = [];
        foreach ($chargeRequestHistories as $chargeRequestHistory) {
            $tradingHistory = new TradingHistory();
            $tradingHistory->shop         = self::getChargeRequestDisplayName($chargeRequestHistory);
            $tradingHistory->spentValue   = $chargeRequestHistory->charge_value;
            $tradingHistory->tradingDate  = Carbon::parse($chargeRequestHistory->created_at);
            $tradingHistory->tradingType  = self::TYPE_CHARGE;
            $ret[] = $tradingHistory;
        }
        return $ret;
    }

    /**
     * チャージ申請履歴の表示名を返す
     *
     * @param ChargeRequestHistory $chargeRequestHistory
     * @return string
     */
    private static function getChargeRequestDisplayName(ChargeRequestHistory $chargeRequestHistory)
    {
        switch ($chargeRequestHistory->processing_status) {
            case ChargeRequestHistory::STATUS_ACCEPTED_RECEPTION:
            case ChargeRequestHistory::STATUS_WAITING_APPLY:
                throw new InvalidArgumentException('非表示のチャージ申請です');
            case ChargeRequestHistory::STATUS_UNPROCESSED_FIRST_CHARGE:
            case ChargeRequestHistory::STATUS_READY:
            case ChargeRequestHistory::STATUS_IS_MAKING_PAYMENT_FILE:
            case ChargeRequestHistory::STATUS_MADE_PAYMENT_FILE:
            case ChargeRequestHistory::STATUS_REQUESTED_CHARGE:
                return $chargeRequestHistory->cause.'（処理中）';
            case ChargeRequestHistory::STATUS_APPLIED_CHARGE:
                return $chargeRequestHistory->cause;
            case ChargeRequestHistory::STATUS_ERROR:
                return $chargeRequestHistory->cause.'（キャンセル）';
        }
        throw new DomainException('未定義の処理状態です');
    }

    /**
     * 取引履歴の配列を取引日でソートする
     * @param TradingHistory[] $tradingHistories 対象の配列
     * @param integer $sort ソート (SORT_DESC or SORT_ASC)
     * @return TradingHistory[] ソート結果
     */
    public static function sortByTradingDate($tradingHistories, $sort = SORT_DESC)
    {
        if ($sort == SORT_DESC) {
            usort($tradingHistories, function(TradingHistory $a, TradingHistory $b) {
                return ($a->tradingDate < $b->tradingDate);
            });
        } else if ($sort == SORT_ASC) {
            usort($tradingHistories, function(TradingHistory $a, TradingHistory $b) {
                return ($a->tradingDate >= $b->tradingDate);
            });
        }
        return $tradingHistories;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'shop'         => $this->shop,
            'spent_value'  => $this->spentValue,
            'trading_date' => $this->tradingDate->format('Y-m-d H:i:s'),
            'trading_type' => $this->tradingType,
        ];
    }
}