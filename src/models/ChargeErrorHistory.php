<?php

namespace app\models;

use app\models\queries\ChargeErrorHistoryQuery;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "charge_error_history".
 * チャージ申請結果がエラーとなった履歴を管理する
 *
 * @property integer $id
 * @property integer $charge_request_history_id
 * @property string  $error_code
 * @property string  $raw_data
 * @property string  $updated_at
 * @property string  $created_at
 *
 * @property ChargeRequestHistory $chargeRequestHistory
 */
class ChargeErrorHistory extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'charge_error_history';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['charge_request_history_id', 'error_code', 'raw_data'], 'required'],
            [['charge_request_history_id'], 'integer'],
            [['raw_data'], 'string'],
            [['error_code'], 'string', 'max' => 20],
            [['charge_request_history_id'], 'exist', 'skipOnError' => true, 'targetClass' => ChargeRequestHistory::className(), 'targetAttribute' => ['charge_request_history_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'                        => 'ID',
            'charge_request_history_id' => 'チャージ申請履歴ID',
            'error_code'                => 'エラーコード',
            'raw_data'                  => 'エラーデータ',
            'updated_at'                => '更新日時',
            'created_at'                => '作成日時',
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getChargeRequestHistory()
    {
        return $this->hasOne(ChargeRequestHistory::className(), ['id' => 'charge_request_history_id']);
    }

    /**
     * @inheritdoc
     * @return ChargeErrorHistoryQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new ChargeErrorHistoryQuery(get_called_class());
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
