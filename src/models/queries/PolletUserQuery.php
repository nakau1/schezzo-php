<?php

namespace app\models\queries;

use app\models\PolletUser;
use app\models\PushNotificationToken;
use yii\db\ActiveQuery;

/**
 * This is the ActiveQuery class for [[PolletUser]].
 *
 * @see PolletUser
 */
class PolletUserQuery extends ActiveQuery
{
    /**
     * 削除されていないユーザに絞ったクエリを返す
     * @return $this
     */
    public function active()
    {
        return $this->andWhere([
            '<>',
            PolletUser::tableName(). '.registration_status',
            PolletUser::STATUS_REMOVED
        ]);
    }

    /**
     * 指定したセディナIDのユーザに絞ったクエリを返す
     * @param string $cedynaId
     * @return $this
     */
    public function cedynaId(string $cedynaId)
    {
        return $this->andWhere([
            PolletUser::tableName(). '.cedyna_id' => $cedynaId,
        ]);
    }

    /**
     * 指定したユーザコードのユーザに絞ったクエリを返す
     * @param string $userCodeSecret
     * @return $this
     */
    public function userCodeSecret(string $userCodeSecret)
    {
        return $this->andWhere([
            PolletUser::tableName(). '.user_code_secret' => $userCodeSecret,
        ]);
    }

    /**
     * 指定したUUIDのユーザに絞ったクエリを返す
     * @param string $uuid
     * @return $this
     */
    public function uuid(string $uuid)
    {
        return $this->joinWith([
            'pushNotificationTokens',
        ])->where([
            PushNotificationToken::tableName() . '.device_id' => $uuid,
        ]);
    }
    
    /**
     * @inheritdoc
     * @return PolletUser[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return PolletUser|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
