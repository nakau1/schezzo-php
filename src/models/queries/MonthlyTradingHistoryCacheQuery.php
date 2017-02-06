<?php

namespace app\models\queries;

use app\models\MonthlyTradingHistoryCache;
use yii\db\ActiveQuery;

/**
 * This is the ActiveQuery class for [[MonthlyTradingHistoryCache]].
 *
 * @see MonthlyTradingHistoryCache
 */
class MonthlyTradingHistoryCacheQuery extends ActiveQuery
{
    /**
     * @inheritdoc
     * @return MonthlyTradingHistoryCache[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return MonthlyTradingHistoryCache|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
