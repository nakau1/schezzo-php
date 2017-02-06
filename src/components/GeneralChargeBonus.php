<?php
namespace app\components;

use yii\base\Component;

/**
 * Class GeneralChargeBonus
 * チャージ時のボーナスを計算するクラス
 *
 * 別のボーナスを作るときはこのクラスを拡張せず
 * 新しいクラスを作成して必要があれば汎化する想定。
 *
 * @package app\components
 */
class GeneralChargeBonus extends Component
{
    const BONUS_RATE = 0.005; // 0.5%

    /**
     * ボーナスを適用する
     *
     * @param int $price
     * @return int
     */
    public function applyTo(int $price): int
    {
        // 参考: http://nkawamura.hatenablog.com/entry/2014/05/21/103806
        return (int)sprintf("%.4f", $price * (1 + self::BONUS_RATE));
    }

    /**
     * ボーナス額を取得する
     *
     * @param int $price
     * @return int
     */
    public static function getPrice(int $price): int
    {
        return (int)sprintf("%.4f", $price * self::BONUS_RATE);
    }

    /**
     * ボーナス率を取得する
     *
     * @return string
     */
    public static function getPercentage(): string
    {
        return sprintf("%.1f", self::BONUS_RATE * 100);
    }
}
