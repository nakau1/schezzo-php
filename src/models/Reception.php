<?php

namespace app\models;

use app\models\queries\ReceptionQuery;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "reception".
 *
 * @property integer $id
 * @property string  $reception_code
 * @property integer $pollet_user_id
 * @property string  $charge_source_code
 * @property integer $charge_request_history_id
 * @property integer $charge_value
 * @property string  $reception_status
 * @property string  $expiry_date
 * @property bool    $by_card_number
 * @property string  $updated_at
 * @property string  $created_at
 *
 * @property ChargeSource         $chargeSource
 * @property ChargeRequestHistory $chargeRequestHistory
 * @property PolletUser           $polletUser
 */
class Reception extends ActiveRecord
{
    /** @var string 指定の受付IDが見つからない */
    const RECEPTION_STATUS_UNKNOWN = 'unknown';
    /** @var string 受付の通知を受けた状態(未申請) */
    const RECEPTION_STATUS_ACCEPTED = 'accepted';
    /** @var string 交換サイト側からpolletシステムに対してチャージ申請の要求をした状態(未完了) */
    const RECEPTION_STATUS_APPLIED = 'applied';
    /** @var string polletシステムが申請されたチャージ受付を処理した状態(完了) */
    const RECEPTION_STATUS_COMPLETED = 'completed';
    /** @var string エラーが発生した状態(再受付が必要) */
    const RECEPTION_STATUS_ERROR = 'error';
    /** @var string 受付有効期限が切れた状態(再受付が必要) */
    const RECEPTION_STATUS_EXPIRED = 'expired';

    // チャージ額増量時に、50万+ボーナス分でカード自体の上限50万を超過してしまうので、
    // APIの1回のチャージ上限としては40万に置くことになった。
    const MAX_PRICE_PER_CHARGE = 400000;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'reception';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['reception_code', 'pollet_user_id', 'charge_source_code', 'charge_request_history_id', 'charge_value', 'reception_status'], 'required'],
            [['pollet_user_id', 'charge_request_history_id', 'charge_value', 'by_card_number'], 'integer'],
            [['reception_code'], 'string', 'max' => 64],
            [['charge_source_code'], 'string', 'max' => 10],
            [['reception_status'], 'string', 'max' => 16],
            [['reception_code'], 'unique', 'targetAttribute' => ['reception_code']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'                        => 'ID',
            'reception_code'            => '受付ID',
            'pollet_user_id'            => 'polletユーザID',
            'charge_source_code'        => 'チャージ元コード',
            'charge_request_history_id' => 'チャージリクエスト履歴ID',
            'charge_value'              => 'チャージ申請額',
            'reception_status'          => '受付ステータス',
            'expiry_date'               => '受付の有効期限',
            'by_card_number'            => 'カード会員番号からユーザを紐付けたかどうか',
            'updated_at'                => '更新日時',
            'created_at'                => '作成日時',
        ];
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
    public function getChargeRequestHistory()
    {
        return $this->hasOne(ChargeRequestHistory::className(), ['id' => 'charge_request_history_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getPolletUser()
    {
        return $this->hasOne(PolletUser::className(), ['id' => 'pollet_user_id']);
    }

    /**
     * @inheritdoc
     * @return ReceptionQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new ReceptionQuery(get_called_class());
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

    // ===============================================================
    // alias method for status
    // ===============================================================

    /**
     * 受付された状態かどうかを返す
     * @return bool
     */
    public function isAccepted()
    {
        return $this->reception_status === self::RECEPTION_STATUS_ACCEPTED;
    }

    /**
     * 申請された状態かどうかを返す
     * @return bool
     */
    public function isApplied()
    {
        return $this->reception_status === self::RECEPTION_STATUS_APPLIED;
    }

    /**
     * 申請処理が終わった状態かどうかを返す
     * @return bool
     */
    public function isCompleted()
    {
        return $this->reception_status === self::RECEPTION_STATUS_COMPLETED;
    }
}
