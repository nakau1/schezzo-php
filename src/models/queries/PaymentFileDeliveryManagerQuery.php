<?php

namespace app\models\queries;
use app\models\PaymentFileDeliveryManager;
use yii\db\ActiveQuery;

/**
 * This is the ActiveQuery class for [[PaymentFileDeliveryManager]].
 *
 * @see PaymentFileDeliveryManager
 */
class PaymentFileDeliveryManagerQuery extends ActiveQuery
{

    /**
     * @inheritdoc
     * @return PaymentFileDeliveryManager[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return PaymentFileDeliveryManager|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
