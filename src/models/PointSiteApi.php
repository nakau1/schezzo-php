<?php

namespace app\models;

use app\models\queries\PointSiteApiQuery;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "point_site_api".
 *
 * @property integer $id
 * @property string  $charge_source_code
 * @property string  $api_name
 * @property string  $url
 * @property string  $publishing_status
 * @property string  $updated_at
 * @property string  $created_at
 *
 * @property ChargeSource $chargeSource
 */
class PointSiteApi extends ActiveRecord
{
    /** @var string APIの種別…交換 */
    const API_NAME_EXCHANGE = 'exchange';
    /** @var string APIの種別…交換キャンセル */
    const API_NAME_CANCEL_EXCHANGE = 'cancel_exchange';
    /** @var string APIの種別…トークン取得*/
    const API_NAME_REQUEST_TOKEN = 'request_token';
    /** @var string APIの種別…ポイント数取得 */
    const API_NAME_FETCH_POINT = 'fetch_point';

    /** @var string 公開状態…公開 */
    const PUBLISHING_STATUS_PUBLIC = 'public';
    /** @var string 公開状態…非公開 */
    const PUBLISHING_STATUS_PRIVATE = 'private';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'point_site_api';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['charge_source_code', 'api_name', 'url', 'publishing_status'], 'required'],
            [['charge_source_code'], 'string', 'max' => 10],
            [['api_name'], 'string', 'max' => 30],
            [['url'], 'string', 'max' => 256],
            [['publishing_status'], 'string', 'max' => 35],
            [['charge_source_code', 'api_name'], 'unique', 'targetAttribute' => ['charge_source_code', 'api_name'], 'message' => 'The combination of Point Site Code and Api Name has already been taken.'],
            [['charge_source_code'], 'exist', 'skipOnError' => true, 'targetClass' => ChargeSource::className(), 'targetAttribute' => ['charge_source_code' => 'charge_source_code']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'                 => 'ID',
            'charge_source_code' => '提携サイトコード',
            'api_name'           => 'API名',
            'url'                => 'URL',
            'publishing_status'  => '公開状態',
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
     * @inheritdoc
     * @return PointSiteApiQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new PointSiteApiQuery(get_called_class());
    }

    /**
     * 公開されているアクセストークン発行リクエストURLを取得する
     *
     * @param string $chargeSourceCode
     * @return string
     */
    public static function findRequestTokenUrl(string $chargeSourceCode)
    {
        $pointSiteApi = self::find()->where([
            'charge_source_code' => $chargeSourceCode,
            'api_name' => self::API_NAME_REQUEST_TOKEN,
            'publishing_status' => self::PUBLISHING_STATUS_PUBLIC,
        ])->one();
        return $pointSiteApi['url'];
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
