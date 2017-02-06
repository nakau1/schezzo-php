<?php
namespace app\models\queries;

use app\models\User;

/**
 * Class UserQuery
 * @package app\models\queries
 */
class UserQuery extends ActiveQuery
{
    /**
     * @inheritdoc
     * @return User[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return User|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
