<?php

namespace app\validators;

use app\helpers\Format;
use app\models\forms\ChargePriceForm;
use app\models\PolletUser;
use yii\validators\Validator;

/**
 * Class ChargePriceClientValidator
 * @package app\validators
 */
class ChargePriceClientValidator extends Validator
{
    // 1回のチャージ金額上限
    const MAX_PRICE_PER_CHARGE = 500000;

    /**
     * @param $model ChargePriceForm
     * @param string $attribute
     */
    public function validateAttribute($model, $attribute)
    {
        if ($model->price < $model->minValue) {
            $this->addError($model, $attribute, $this->getMessageLessMinValue($model->minValue));
        }

        if ($model->price > $model->maxValue) {
            $this->addError($model, $attribute, $this->getMessageOverMaxValue($model->maxValue));
        }

        if ($model->price > self::MAX_PRICE_PER_CHARGE) {
            $this->addError($model, $attribute, $this->getMessageOverMaxPerChargeValue());
        }
    }

    /**
     * @param $model ChargePriceForm
     * @param $attribute
     * @param $view
     * @return string
     */
    public function clientValidateAttribute($model, $attribute, $view)
    {
        $conditions = [
            'value < '. $model->minValue => $this->getMessageLessMinValue($model->minValue),
            'value > '. $model->maxValue => $this->getMessageOverMaxValue($model->maxValue),
            'value > '. self::MAX_PRICE_PER_CHARGE => $this->getMessageOverMaxPerChargeValue(),
        ];

        $ret = '';
        foreach ($conditions as $condition => $errorMessage) {
            $ret .= 'if ('. $condition .') {';
            $ret .= 'messages.push("'. $errorMessage .'");';
            $ret .= '}';
        }
        return $ret;
    }

    /**
     * @param $minValue integer
     * @return string
     */
    private function getMessageLessMinValue($minValue)
    {
        return Format::formattedJapaneseCurrency($minValue) . ' 以上を入力してください。';
    }

    /**
     * @param $maxValue integer
     * @return string
     */
    private function getMessageOverMaxValue($maxValue)
    {
        return Format::formattedJapaneseCurrency($maxValue) . ' 以下を入力してください。';
    }

    /**
     * @return string
     */
    private function getMessageOverMaxPerChargeValue()
    {
        return '1回にチャージできる金額は' . Format::formattedJapaneseCurrency(self::MAX_PRICE_PER_CHARGE) . 'までです。';
    }
}
