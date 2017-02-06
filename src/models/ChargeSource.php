<?php

namespace app\models;

use app\models\charge_source_cooperation\ChargeSourceCooperation;
use app\models\exceptions\PointSiteApiCooperation\RequestFailedException;
use app\models\queries\ChargeSourceQuery;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "charge_source".
 *
 * @property integer $id
 * @property string  $charge_source_code
 * @property string  $api_key
 * @property string  $site_name
 * @property integer $min_value 最低交換金額
 * @property integer $card_issue_fee カード発行手数料
 * @property string  $url
 * @property string  $icon_image_url
 * @property string  $denomination ポイントの単位
 * @property integer $introduce_charge_rate_point 紹介時交換レート表示値（ポイント）
 * @property integer $introduce_charge_rate_price 紹介時交換レート表示値（現金）
 * @property string  $description
 * @property string  $auth_url 要認証時に飛ばす先のURL
 * @property string  $publishing_status
 * @property string  $cooperation_type
 * @property string  $updated_at
 * @property string  $created_at
 *
 * @property ChargeRequestHistory[] $chargeRequestHistories
 * @property PointSiteApi[]         $apis
 * @property PointSiteToken[]       $tokens
 * @property PolletUser[]           $polletUsers
 *
 * @property integer $myValidPoint:
 */
class ChargeSource extends ActiveRecord
{
    /** @var string 公開状態…公開 */
    const PUBLISHING_STATUS_PUBLIC = 'public';
    /** @var string 公開状態…非公開*/
    const PUBLISHING_STATUS_PRIVATE = 'private';

    /** @var string 提携タイプ…ポイントサイトのAPIを使う方式 */
    const COOPERATION_TYPE_POINT_SITE_API = 'point_site_api';
    /** @var string 定型タイプ…polletの交換APIを使う方式 */
    const COOPERATION_TYPE_POLLET_API = 'pollet_api';

    /** @var bool 認証されているかどうか */
    public $isAuthorized = false;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'charge_source';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['charge_source_code', 'site_name', 'min_value', 'url', 'introduce_charge_rate_point', 'introduce_charge_rate_price', 'description', 'publishing_status', 'cooperation_type'], 'required'],
            [['min_value', 'card_issue_fee', 'introduce_charge_rate_point', 'introduce_charge_rate_price'], 'integer'],
            [['description'], 'string'],
            [['charge_source_code'], 'string', 'max' => 10],
            [['site_name'], 'string', 'max' => 50],
            [['url', 'api_key', 'icon_image_url', 'auth_url'], 'string', 'max' => 256],
            [['denomination'], 'string', 'max' => 16],
            [['publishing_status', 'cooperation_type'], 'string', 'max' => 35],
            [['charge_source_code'], 'unique'],
            [['site_name'], 'unique'],
        ];
    }

    /**
     * 承認が必要な提携タイプの設定
     * @return array
     */
    public static function typesRequireAuthorization()
    {
        return [
            self::COOPERATION_TYPE_POINT_SITE_API,
        ];
    }

    /**
     * 初回チャージ時に選択できる提携タイプの設定
     * @return array
     */
    public static function typesCanChargeAtFirst()
    {
        return [
            self::COOPERATION_TYPE_POINT_SITE_API,
            self::COOPERATION_TYPE_POLLET_API,
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'                          => 'ID',
            'charge_source_code'          => '提携サイトコード',
            'api_key'                     => 'polletから提供するAPIキー',
            'site_name'                   => '表示用サイト名',
            'min_value'                   => '最低金額',
            'card_issue_fee'              => '初回カード発行手数料',
            'url'                         => 'サイトURL',
            'icon_image_url'              => 'アイコン画像URL',
            'denomination'                => 'ポイント単位',
            'introduce_charge_rate_point' => '紹介時交換レート表示値（ポイント）',
            'introduce_charge_rate_price' => '紹介時交換レート表示値（現金）',
            'description'                 => 'サイトの説明',
            'auth_url'                    => '認証URL',
            'publishing_status'           => '公開状態',
            'cooperation_type'            => '提携タイプ',
            'updated_at'                  => '更新日時',
            'created_at'                  => '作成日時',
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getChargeRequestHistories()
    {
        return $this->hasMany(ChargeRequestHistory::className(), ['charge_source_code' => 'charge_source_code']);
    }

    /**
     * @return ActiveQuery
     */
    public function getApis()
    {
        return $this->hasMany(PointSiteApi::className(), ['charge_source_code' => 'charge_source_code']);
    }

    /**
     * @return ActiveQuery
     */
    public function getTokens()
    {
        return $this->hasMany(PointSiteToken::className(), ['charge_source_code' => 'charge_source_code']);
    }

    /**
     * @return ActiveQuery
     */
    public function getPolletUsers()
    {
        return $this->hasMany(PolletUser::className(), ['id' => 'pollet_user_id'])->viaTable('point_site_token', ['charge_source_code' => 'charge_source_code']);
    }

    /**
     * @inheritdoc
     * @return ChargeSourceQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new ChargeSourceQuery(get_called_class());
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
    // dynamic getter properties
    // ===============================================================

    /**
     * ポイントサイトのポイント残高を返す
     * @return int|bool
     */
    public function getMyValidPoint()
    {
        /** @var PolletUser $user */
        $user = \Yii::$app->user->identity;
        try {
            return ChargeSourceCooperation::getValidPointAsCash($this, $user);
        } catch (RequestFailedException $e) {
            return false;
        }
    }

    /**
     * 承認が必要かどうかを返す
     * @return bool
     */
    public function requiresAuthorization()
    {
        return in_array($this->cooperation_type, self::typesRequireAuthorization(), true);
    }

    /**
     * 公開状態かどうかを返す
     * @return bool
     */
    public function isPublic(): bool
    {
        return $this->publishing_status === self::PUBLISHING_STATUS_PUBLIC;
    }
}
