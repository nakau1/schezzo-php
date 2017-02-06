<?php

namespace app\models;

use app\models\queries\RegisterCampaignPointPercentageQuery;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "register_campaign_point_percentage".
 *
 * @property integer $id
 * @property integer $period
 * @property string  $point_rate
 * @property string  $begin_date
 * @property string  $end_date
 * @property string  $publishing_status
 * @property string  $updated_at
 * @property string  $created_at
 */
class RegisterCampaignPointPercentage extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'register_campaign_point_percentage';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['period', 'point_rate', 'publishing_status'], 'required'],
            [['period'], 'integer'],
            [['point_rate'], 'number'],
            [['publishing_status'], 'string', 'max' => 35],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'                => 'ID',
            'period'            => '登録日からの日数',
            'point_rate'        => '還元率',
            'begin_date'        => '開始日時',
            'end_date'          => '終了日時',
            'publishing_status' => '公開状態',
            'updated_at'        => '更新日時',
            'created_at'        => '作成日時',
        ];
    }

    /**
     * @inheritdoc
     * @return RegisterCampaignPointPercentageQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new RegisterCampaignPointPercentageQuery(get_called_class());
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
