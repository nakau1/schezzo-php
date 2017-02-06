<?php

namespace app\models\queries;

use app\models\ChargeErrorHistory;
use yii\db\ActiveQuery;

/**
 * This is the ActiveQuery class for [[ChargeErrorHistory]].
 *
 * @see ChargeErrorHistory
 */
class ChargeErrorHistoryQuery extends ActiveQuery
{
    /**
     * @inheritdoc
     * @return ChargeErrorHistory[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return ChargeErrorHistory|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
