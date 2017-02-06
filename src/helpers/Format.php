<?php

namespace app\helpers;

use Yii;

/**
 * Class Format
 * 頻度の高いフォーマット処理を行うヘルパクラス
 * @package app\helpers
 */
class Format
{
    /**
     * タイムスタンプの値から'Y/m/d'形式の日付文字列を取得する
     * @param $timestamp string タイムスタンプ
     * @return string 'Y/m/d'形式の日付文字列
     */
    public static function formattedDate($timestamp)
    {
        return Yii::$app->formatter->asDatetime($timestamp, 'php:Y/m/d');
    }

    /**
     * タイムスタンプの値から'Y年m月d日'形式の日付文字列を取得する
     * @param $timestamp string タイムスタンプ
     * @return string 'Y年m月d日'形式の日付文字列
     */
    public static function formattedJapaneseDate($timestamp)
    {
        return Yii::$app->formatter->asDatetime($timestamp, 'php:Y年m月d日');
    }

    /**
     * タイムスタンプの値から'j日'形式の日付文字列を取得する
     * @param $timestamp string タイムスタンプ
     * @return string 'j日'形式の日付文字列
     */
    public static function formattedDay($timestamp)
    {
        return Yii::$app->formatter->asDatetime($timestamp, 'php:j日');
    }

    /**
     * 整数値から3桁区切りの文字列を取得する
     * @param $value int 整数値
     * @param $ifNullToEmpty bool nullが与えられた場合に空文字を返すかどうか(falseの場合はNullを0扱い)
     * @return string 3桁区切りの文字列
     */
    public static function formattedNumber($value, $ifNullToEmpty = false)
    {
        if (is_null($value) && $ifNullToEmpty) {
            return '';
        }
        return Yii::$app->formatter->asDecimal($value, 0);
    }

    /**
     * 整数値から"¥ nn"形式の金額表示用の文字列を取得する
     * @param $value int 整数値
     * @return string 金額表示用の文字列
     */
    public static function formattedCurrency($value)
    {
        return '¥ ' . self::formattedNumber($value);
    }

    /**
     * 整数値から"nn円"形式の金額表示用の文字列を取得する
     * @param $value int 整数値
     * @return string 金額表示用の文字列
     */
    public static function formattedJapaneseCurrency($value)
    {
        return self::formattedNumber($value) . '円';
    }
}
