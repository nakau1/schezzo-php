<?php

namespace app\models;

use app\models\queries\CardValueCacheQuery;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "card_value_cache".
 *
 * @property integer $id
 * @property integer $pollet_user_id
 * @property integer $value カード残高合計
 * @property string  $updated_at
 * @property string  $created_at
 *
 * @property PolletUser $polletUser
 */
class CardValueCache extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'card_value_cache';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['pollet_user_id', 'value'], 'required'],
            [['pollet_user_id', 'value'], 'integer'],
            [['pollet_user_id'], 'unique'],
            [['pollet_user_id'], 'exist', 'skipOnError' => true, 'targetClass' => PolletUser::className(), 'targetAttribute' => ['pollet_user_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'             => 'ID',
            'pollet_user_id' => 'ポレットユーザID',
            'value'          => 'カード残高合計',
            'updated_at'     => '更新日時',
            'created_at'     => '作成日時',
        ];
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
     * @return CardValueCacheQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new CardValueCacheQuery(get_called_class());
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
     * 最新のキャッシュを保存する
     *
     * @param int $value
     * @param int $polletUserId
     * @return bool
     */
    public static function store(int $value, int $polletUserId)
    {
        $cache = self::find()->where([
            'pollet_user_id' => $polletUserId,
        ])->one() ?? new self;
        $cache->pollet_user_id = $polletUserId;
        $cache->value = $value;

        return $cache->save();
    }
}
