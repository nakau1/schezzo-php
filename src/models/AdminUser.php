<?php

namespace app\models;

use app\models\queries\AdminUserQuery;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "admin_user".
 *
 * @property integer $id
 * @property string  $name
 * @property string  $updated_at
 * @property string  $created_at
 *
 * @property InquiryReply[] $inquiryReplies
 */
class AdminUser extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'admin_user';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 32],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'         => 'ID',
            'name'       => 'ユーザ名',
            'updated_at' => '更新日時',
            'created_at' => '作成日時',
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getInquiryReplies()
    {
        return $this->hasMany(InquiryReply::className(), ['admin_user_id' => 'id']);
    }

    /**
     * @inheritdoc
     * @return AdminUserQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new AdminUserQuery(get_called_class());
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'value' => date('Y-m-d H:i:s'),
            ],
        ];
    }
}
