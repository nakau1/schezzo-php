<?php

namespace app\helpers;

/**
 * Class YearMonth
 * @package app\helpers
 */
class YearMonth
{
    const PARAM_FORMAT       = 'ym';
    const DATE_STRING_FORMAT = 'Y-m-d H:i:s';

    const RETROACTIVE_MONTHS = 5; // 5ヶ月間遡る
    /**
     * 年月のパラメータ文字列から年と月に分けた整数の配列を返す
     * @param $month string 'yymm'の形式の月(2016年9月は'1609')
     * @return array [年, 月]
     */
    public static function divideMonthString($month)
    {
        $y = (int)substr($month, 0, 2) + 2000;
        $m = (int)substr($month, 2, 2);
        return [$y, $m];
    }

    /**
     * URLパラメータで付与する次月用の文字列を返す('ym'形式)
     *
     * 現在月を含めて未来を渡した場合はnullを返す
     *
     * @param $month
     * @return false|null|string
     */
    public static function getNextMonthString($month)
    {
        if ((int)$month >= (int)date(self::PARAM_FORMAT)) {
            return null;
        }
        list($y, $m) = self::divideMonthString($month);
        return date(self::PARAM_FORMAT, mktime(0, 0, 0, $m + 1, 1, $y));
    }

    /**
     * URLパラメータで付与する前月用の文字列を返す('ym'形式)
     *
     * @param $month
     * @return false|string
     */
    public static function getPrevMonthString($month)
    {
        // 5ヶ月以上は遡れない
        list($currentY, $currentM) = self::divideMonthString(date(self::PARAM_FORMAT));
        $min = date(self::PARAM_FORMAT, mktime(0, 0, 0, $currentM - (self::RETROACTIVE_MONTHS - 1), 1, $currentY));
        if ((int)$month <= (int)$min) {
            return null;
        }

        list($y, $m) = self::divideMonthString($month);
        return date(self::PARAM_FORMAT, mktime(0, 0, 0, $m - 1, 1, $y));
    }

    /**
     * 年月のパラメータ文字列からその年月の最初と最後の日付文字列('Y-m-d H:i:s'形式)を配列で返す
     * @param $month string 'yymm'の形式の月(2016年9月は'1609')
     * @return array [From, To]
     */
    public static function getDateStringsFromTo($month)
    {
        list($from, $to) = self::getTimestampsFromTo($month);
        return [
            date(self::DATE_STRING_FORMAT, $from),
            date(self::DATE_STRING_FORMAT, $to),
        ];
    }

    /**
     * 年月のパラメータ文字列からその年月の最初と最後のタイムスタンプを配列で返す
     * @param $month string 'yymm'の形式の月(2016年9月は'1609')
     * @return array [From, To]
     */
    public static function getTimestampsFromTo($month)
    {
        list($y, $m) = self::divideMonthString($month);
        return [
            mktime(0,  0,  0,  $m,     1, $y),
            mktime(23, 59, 59, $m + 1, 0, $y),
        ];
    }
}