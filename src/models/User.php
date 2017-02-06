<?php
namespace app\models;

use app\models\queries\UserQuery;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "user".
 *
 * @property integer $id
 * @property string  $account
 * @property string  $name
 * @property string  $email
 * @property string  $status
 * @property string  $updated_at
 * @property string  $created_at
 */
class User extends ActiveRecord implements IdentityInterface
{
    public static function tableName()
    {
        return 'pollet_user';
    }

    public static function find()
    {
        return new UserQuery(get_called_class());
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['account', 'name'], 'required'],
            [['account'], 'string', 'max' => 20],
            [['name', 'email'], 'string', 'max' => 256],
            [['status'], 'string', 'max' => 32],
            [['account'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'         => 'ID',
            'account'    => 'アカウント名',
            'name'       => 'ユーザ名',
            'email'      => 'メールアドレス',
            'status'     => 'ステータス',
            'updated_at' => '更新日時',
            'created_at' => '作成日時',
        ];
    }

    // ===============================================================
    // implementation for IdentityInterface
    // ===============================================================

    /**
     * @param int|string $id
     * @return User|array|null
     */
    public static function findIdentity($id)
    {
        return self::find()->andWhere([
            self::tableName() . '.id' => $id,
        ])->one();
    }

    /**
     * @inheritdoc
     * @param mixed $token
     * @param null  $type
     * @return User|array|null
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne([
            'account' => $token,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->id == $authKey;
    }
}
