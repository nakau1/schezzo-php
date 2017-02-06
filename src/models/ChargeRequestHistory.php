<?php

namespace app\models;

use app\models\queries\ChargeRequestHistoryQuery;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "charge_request_history".
 *
 * @property integer $id
 * @property integer $pollet_user_id
 * @property integer $charge_source_code
 * @property integer $charge_value       チャージ額
 * @property integer $exchange_value     ポイントサイト交換額
 * @property string  $cause
 * @property string  $processing_status
 * @property string  $updated_at
 * @property string  $created_at
 *
 * @property ChargeErrorHistory[] $chargeErrorHistories
 * @property ChargeSource         $chargeSource
 * @property PolletUser           $polletUser
 * @property Reception            $reception
 */
class ChargeRequestHistory extends ActiveRecord
{
    // ステータスを追加する際には、既存の処理の条件に追加する必要がないか確認してください。
    const STATUS_ACCEPTED_RECEPTION       = 'accepted_reception';
    const STATUS_WAITING_APPLY            = 'waiting_apply';
    const STATUS_UNPROCESSED_FIRST_CHARGE = 'unprocessed_first_charge';
    const STATUS_READY                    = 'ready';
    const STATUS_IS_MAKING_PAYMENT_FILE   = 'is_making_payment_file';
    const STATUS_MADE_PAYMENT_FILE        = 'made_payment_file';
    const STATUS_REQUESTED_CHARGE         = 'requested_charge';
    const STATUS_APPLIED_CHARGE           = 'applied_charge';
    const STATUS_ERROR                    = 'error';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'charge_request_history';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['pollet_user_id', 'charge_source_code', 'charge_value', 'processing_status'], 'required'],
            [['pollet_user_id', 'charge_value', 'exchange_value'], 'integer'],
            // カードの仕様に合わせる（1回あたり最大50万）
            [['charge_value', 'exchange_value'], 'integer', 'min' => 1, 'max' => 500000],
            [['charge_source_code'], 'string', 'max' => 10],
            [['cause'], 'string', 'max' => 100],
            [['processing_status'], 'string', 'max' => 35],
            [['charge_source_code'], 'exist', 'skipOnError' => true, 'targetClass' => ChargeSource::className(), 'targetAttribute' => ['charge_source_code' => 'charge_source_code']],
            [['pollet_user_id'], 'exist', 'skipOnError' => true, 'targetClass' => PolletUser::className(), 'targetAttribute' => ['pollet_user_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'                 => 'ID',
            'pollet_user_id'     => 'ポレットユーザID',
            'charge_source_code' => 'チャージ元コード',
            'charge_value'       => 'チャージ額',
            'exchange_value'     => 'ポイントサイト交換額',
            'cause'              => 'チャージ理由',
            'processing_status'  => '処理状態',
            'updated_at'         => '更新日時',
            'created_at'         => '作成日時',
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getChargeErrorHistories()
    {
        return $this->hasMany(ChargeErrorHistory::className(), ['charge_request_history_id' => 'id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getChargeSource()
    {
        return $this->hasOne(ChargeSource::className(), ['charge_source_code' => 'charge_source_code']);
    }

    /**
     * @return ActiveQuery
     */
    public function getPolletUser()
    {
        return $this->hasOne(PolletUser::className(), ['id' => 'pollet_user_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getReception()
    {
        return $this->hasOne(Reception::className(), ['charge_request_history_id' => 'id']);
    }

    /**
     * @inheritdoc
     * @return ChargeRequestHistoryQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new ChargeRequestHistoryQuery(get_called_class());
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

    /**
     * 新しくチャージ申請履歴情報を追加する
     * 必要に応じてトランザクションを張ってください
     *
     * @param ChargeSource $chargeSource
     * @param PolletUser $user
     * @param int $price         カードへチャージする額
     * @param int $exchangePrice チャージ元から差し引く額
     * @param string $status     処理状態
     * @return ChargeRequestHistory
     * @throws \Exception
     */
    public static function add(
        ChargeSource $chargeSource,
        PolletUser $user,
        int $price,
        int $exchangePrice,
        string $status
    ) {
        $chargeRequest = new self;
        $chargeRequest->pollet_user_id     = $user->id;
        $chargeRequest->charge_source_code = $chargeSource->charge_source_code;
        $chargeRequest->charge_value       = $price;
        $chargeRequest->exchange_value     = $exchangePrice;
        $chargeRequest->cause              = "{$chargeSource->site_name}からチャージ（0.5％込）";
        $chargeRequest->processing_status  = $status;

        if (!$chargeRequest->save()) {
            throw new \Exception('failed add charge_request_history. '.$chargeRequest->firstErrors);
        }

        return $chargeRequest;
    }
}
