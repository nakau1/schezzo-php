<?php

namespace app\models;

use app\models\queries\PointSiteTokenQuery;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "point_site_token".
 *
 * @property integer $id
 * @property integer $pollet_user_id
 * @property string $charge_source_code
 * @property string $token
 * @property string  $updated_at
 * @property string  $created_at
 *
 * @property ChargeSource $chargeSource
 * @property PolletUser $polletUser
 */
class PointSiteToken extends ActiveRecord
{
    const PUBLISHING_STATUS_PUBLIC = 'public';
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'point_site_token';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['pollet_user_id', 'charge_source_code', 'token'], 'required'],
            [['pollet_user_id'], 'integer'],
            [['charge_source_code'], 'string', 'max' => 10],
            [['token'], 'string', 'max' => 256],
            [['pollet_user_id', 'charge_source_code'], 'unique', 'targetAttribute' => ['pollet_user_id', 'charge_source_code'], 'message' => 'The combination of Pollet User ID and Point Site Code has already been taken.'],
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
            'charge_source_code' => '提携サイトコード',
            'token'              => 'トークン',
            'updated_at'         => '更新日時',
            'created_at'         => '作成日時',
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
    public function getPolletUser()
    {
        return $this->hasOne(PolletUser::className(), ['id' => 'pollet_user_id']);
    }

    /**
     * @inheritdoc
     * @return PointSiteTokenQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new PointSiteTokenQuery(get_called_class());
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
     * 指定したユーザと紐づく提携サイト連携レコードを追加する
     * @param $polletUserId     integer ポレットユーザID
     * @param $token            string トークン
     * @param $chargeSourceCode string 提携サイトコード
     * @return bool 成功 / 失敗
     */
    public static function add(int $polletUserId, string $token, string $chargeSourceCode)
    {
        $trans = Yii::$app->db->beginTransaction();
        try {
            $pointSiteToken = self::findOne([
                'pollet_user_id' => $polletUserId,
                'charge_source_code' => $chargeSourceCode
            ]);

            if (is_null($pointSiteToken)) {
                $pointSiteToken = new PointSiteToken();
            }
            $pointSiteToken->pollet_user_id = $polletUserId;
            $pointSiteToken->token = $token;
            $pointSiteToken->charge_source_code = $chargeSourceCode;
            $result = $pointSiteToken->save();

            if (!$result) {
                throw new \Exception('failed add token.');
            }
            $trans->commit();
            return true;
        } catch (\Exception $e) {
            $trans->rollBack();
            return false;
        }
    }
}
