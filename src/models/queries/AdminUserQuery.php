<?php

namespace app\models\queries;

use app\models\AdminUser;
use yii\db\ActiveQuery;

/**
 * This is the ActiveQuery class for [[AdminUser]].
 *
 * @see AdminUser
 */
class AdminUserQuery extends ActiveQuery
{
    /**
     * @inheritdoc
     * @return AdminUser[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return AdminUser|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
