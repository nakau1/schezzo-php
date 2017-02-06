<?php

namespace app\models;

use app\models\queries\PaymentFileDeliveryManagerQuery;
use yii\behaviors\TimestampBehavior;
use Yii;
use yii\db;
use yii\db\ActiveRecord;
use app\models\exceptions\PaymentFileDeliveryManager\UnexpectedDataException;

/**
 * This is the model class for table "payment_file_delivery_manager".
 *
 * @property integer $id
 * @property integer $is_sending
 * @property string  $updated_at
 * @property string  $created_at
 */
class PaymentFileDeliveryManager extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'payment_file_delivery_manager';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['is_sending'], 'required'],
            [['is_sending'], 'integer'],
        ];
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
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'         => 'ID',
            'is_sending' => '伝送中',
            'updated_at' => '更新日時',
            'created_at' => '作成日時',
        ];
    }

    /**
     * @inheritdoc
     * @return PaymentFileDeliveryManagerQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new PaymentFileDeliveryManagerQuery(get_called_class());
    }

    /**
     * 伝送中にする
     */
    public static function sending()
    {
        $record = self::find()->one() ?? new static();
        // フラグをたてる
        $record->is_sending = 1;
        $record->save();
    }

    /**
     * 伝送中じゃない状態にする
     */
    public static function notSending()
    {
        $record = self::find()->one() ?? new static();
        // フラグを落とす
        $record->is_sending = 0;
        $record->save();
    }

    /**
     * 伝送中か否か
     * 伝送中ならtrue、ファイルを送っていない状態だとfalse
     * @return bool
     */
    public static function isSending()
    {
        /**
         * データが2件以上生成される運用は想定していないので、2件以上ある場合は異常終了
         * データが0件の時は削除した可能性があるため、伝送中か伝送中じゃないか判断つかないので異常終了
         */
        $recordCount = self::find()->count();
        if ($recordCount == 0 || $recordCount >= 2) {
            throw new UnexpectedDataException();
        }
        $record = self::find()->one();
        return $record->is_sending == true;
    }
}
