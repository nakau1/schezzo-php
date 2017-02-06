<?php
namespace app\helpers;

use Yii;

/**
 * Class Format
 * @package app\helpers
 */
class Format
{
    /**
     * 整数値から3桁区切りの文字列を取得する
     * @param $value int 整数値
     * @return string 3桁区切りの文字列
     */
    public static function formattedNumber($value)
    {
        if (is_null($value)) {
            return '';
        }
        return Yii::$app->formatter->asDecimal($value, 0);
    }
}
