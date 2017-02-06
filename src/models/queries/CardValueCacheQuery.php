<?php

namespace app\models\queries;

use app\models\CardValueCache;
use yii\db\ActiveQuery;

/**
 * This is the ActiveQuery class for [[CardValueCache]].
 *
 * @see CardValueCache
 */
class CardValueCacheQuery extends ActiveQuery
{
    /**
     * @inheritdoc
     * @return CardValueCache[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return CardValueCache|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
