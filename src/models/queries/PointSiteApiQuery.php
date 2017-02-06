<?php

namespace app\models\queries;

use app\models\PointSiteApi;
use yii\db\ActiveQuery;

/**
 * This is the ActiveQuery class for [[PointSiteApi]].
 *
 * @see PointSiteApi
 */
class PointSiteApiQuery extends ActiveQuery
{
    /**
     * @inheritdoc
     * @return PointSiteApi[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return PointSiteApi|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
