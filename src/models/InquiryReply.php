<?php

namespace app\models;

use app\models\queries\InquiryReplyQuery;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "inquiry_reply".
 *
 * @property integer $id
 * @property integer $inquiry_id
 * @property integer $admin_user_id
 * @property string  $content
 * @property string  $updated_at
 * @property string  $created_at
 *
 * @property AdminUser $adminUser
 */
class InquiryReply extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'inquiry_reply';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['inquiry_id', 'admin_user_id', 'content'], 'required'],
            [['inquiry_id', 'admin_user_id'], 'integer'],
            [['content'], 'string'],
            [['admin_user_id'], 'exist', 'skipOnError' => true, 'targetClass' => AdminUser::className(), 'targetAttribute' => ['admin_user_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'            => 'ID',
            'inquiry_id'    => '返信先問い合わせID',
            'admin_user_id' => '管理ユーザID',
            'content'       => '内容',
            'updated_at'    => '更新日時',
            'created_at'    => '作成日時',
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getAdminUser()
    {
        return $this->hasOne(AdminUser::className(), ['id' => 'admin_user_id']);
    }

    /**
     * @inheritdoc
     * @return InquiryReplyQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new InquiryReplyQuery(get_called_class());
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
