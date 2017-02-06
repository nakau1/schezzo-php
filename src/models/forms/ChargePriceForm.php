<?php

namespace app\models\forms;

use yii\base\Model;
use app\validators\ChargePriceClientValidator;

/**
 * Class ChargePriceForm
 * @package app\models\forms
 *
 * @property integer $price
 * @property integer $chargeRemain
 * @property integer $minValue
 * @property integer $maxValue
 * @property integer $cardIssueFee
 */
class ChargePriceForm extends Model
{
    public $price = 0;
    public $chargeRemain = 0;
    public $minValue = 0;
    public $cardIssueFee = 0;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            // price are required
            ['price', 'required', 'message' => 'チャージ額を入力してください。'],
            ['price', 'integer'],
            ['price', ChargePriceClientValidator::className()],
        ];
    }

    /**
     * ユーザが入力できるチャージ可能最高額を返すプロパティ
     * @return int
     */
    public function getMaxValue()
    {
        return $this->chargeRemain;
    }
}
