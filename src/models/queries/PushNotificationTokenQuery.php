<?php

namespace app\models\queries;

use app\models\PushNotificationToken;
use yii\db\ActiveQuery;

/**
 * This is the ActiveQuery class for [[PushNotificationToken]].
 *
 * @see PushNotificationToken
 */
class PushNotificationTokenQuery extends ActiveQuery
{
    /**
     * @inheritdoc
     * @return PushNotificationToken[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return PushNotificationToken|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
