<?php

namespace app\models\queries;

use app\models\BatchManagement;
use yii\db\ActiveQuery;

/**
 * This is the ActiveQuery class for [[BatchManagement]].
 *
 * @see BatchManagement
 */
class BatchManagementQuery extends ActiveQuery
{
    /**
     * @inheritdoc
     * @return BatchManagement[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return BatchManagement|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
