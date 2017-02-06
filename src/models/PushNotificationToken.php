<?php

namespace app\models;

use app\models\queries\PushNotificationTokenQuery;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "push_notification_token".
 *
 * @property integer $id
 * @property integer $pollet_user_id
 * @property string  $device_id
 * @property string  $token
 * @property string  $platform
 * @property integer $is_active 利用されている端末かどうか
 * @property string  $updated_at
 * @property string  $created_at
 *
 * @property PolletUser $polletUser
 */
class PushNotificationToken extends ActiveRecord
{
    const PLATFORM_ANDROID = 'android';
    const PLATFORM_IOS     = 'ios';

    public $number_of_unread_informations = 0;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'push_notification_token';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['pollet_user_id', 'device_id', 'platform'], 'required'],
            [['pollet_user_id', 'is_active'], 'integer'],
            [['device_id', 'token'], 'string', 'max' => 256],
            [['platform'], 'string', 'max' => 20],
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
            'device_id'      => 'デバイスID',
            'token'          => 'デバイストークン',
            'platform'       => 'プラットフォーム',
            'is_active'      => 'アクティブ端末フラグ',
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
     * @return PushNotificationTokenQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new PushNotificationTokenQuery(get_called_class());
    }

    /**
     * 指定したプラットフォームの有効なデバイストークンの配列を返す
     * @param string $platform プラットフォーム
     * @return array [バッジ件数 => 通知用トークン[]]
     */
    public static function findDeviceTokens($platform)
    {
        $tokenTable   = self::tableName();
        $userTable    = PolletUser::tableName();
        $infoTable    = Information::tableName();
        $openingTable = PushInformationOpening::tableName();

        //
        $totalInformationCountQuery = Information::find()->published()->important()
            ->select([
                new Expression('COUNT(*)'),
            ]);
        $totalInformationCountSQL = $totalInformationCountQuery->createCommand()->rawSql;

        //
        $unreadInformationCountQuery = Information::find()->published()->important()
            ->select([
                $openingTable.'.pollet_user_id',
                new Expression('COUNT(*) AS count'),
            ])
            ->from($openingTable)
            ->innerJoin($infoTable, [
                $openingTable.'.information_id' => new Expression($infoTable.'.id'),
            ])
            ->groupBy([
                $openingTable.'.pollet_user_id',
            ]);
        $unreadInformationCountSQL = $unreadInformationCountQuery->createCommand()->rawSql;

        //
        $query = self::find()
            ->select([
                $tokenTable.'.token',
                new Expression('(' . $totalInformationCountSQL .') - COALESCE(read_informations.count, 0) AS number_of_unread_informations'),
            ])
            ->innerJoin($userTable, [
                $tokenTable.'.pollet_user_id' => new Expression($userTable.'.id'),
            ])
            ->leftJoin((new Expression('(' . $unreadInformationCountSQL .') AS read_informations'))->expression, [
                $tokenTable.'.pollet_user_id' => new Expression('read_informations.pollet_user_id'),
            ])
            ->andWhere([
                PushNotificationToken::tableName() . '.platform' => $platform,
                PushNotificationToken::tableName() . '.is_active' => 1,
            ])
            ->andWhere([
                '<>',
                PushNotificationToken::tableName() . '.token',
                '',
            ])
            ->andWhere([
                '<>',
                PolletUser::tableName() . '.registration_status',
                PolletUser::STATUS_REMOVED,
            ]);
        
        $ret = [];
        foreach ($query->all() as $row) {
            $ret[$row->number_of_unread_informations][] = $row->token;
        }
        return $ret;

        /* 生成されるSQL例
        -------------------------------------------------
        SELECT
            `push_notification_token`.`token`,
            (
                SELECT
                    COUNT(*)
                FROM
                    `information`
                WHERE
                    `information`.`publishing_status`='public'
                    AND
                    `information`.`begin_date` <= '2016-12-02 09:59:55'
                    AND
                    `information`.`end_date` >= '2016-12-02 09:59:55'
                    AND
                    `information`.`is_important`=1
            ) - COALESCE(read_informations.count, 0) AS number_of_unread_informations
        FROM
            `push_notification_token`
        INNER JOIN
            `pollet_user`
            ON
            `push_notification_token`.`pollet_user_id`=pollet_user.id
        LEFT JOIN
            (
                SELECT
                    `push_information_opening`.`pollet_user_id`,
                    COUNT(*) AS count
                FROM
                    `push_information_opening`
                INNER JOIN
                    `information`
                    ON
                    `push_information_opening`.`information_id`=information.id
                WHERE
                    `information`.`publishing_status`='public'
                    AND
                    `information`.`begin_date` <= '2016-12-02 09:59:55'
                    AND
                    `information`.`end_date` >= '2016-12-02 09:59:55'
                    AND
                    `information`.`is_important`=1
                GROUP BY
                    `push_information_opening`.`pollet_user_id`
            ) AS read_informations
            ON
            `push_notification_token`.`pollet_user_id`=read_informations.pollet_user_id
        WHERE
            `push_notification_token`.`platform`='ios'
            AND
            `push_notification_token`.`is_active`=1
            AND
            `push_notification_token`.`token` <> ''
            AND
            `pollet_user`.`registration_status` <> 'removed'

        */
    }
    
    /**
     * 対象のIDのレコードを指定したIDのレコードで上書きする
     * @param int $sourceUserId
     * @param int $targetUserId
     * @return bool
     */
    public static function override($sourceUserId, $targetUserId)
    {
        /** @var PushNotificationToken $sourceToken */
        $sourceToken = PushNotificationToken::find()->where([
            'pollet_user_id' => $sourceUserId,
        ])->one();
        if (!$sourceToken) {
            return true;
        }

        /** @var PushNotificationToken $targetToken */
        $targetToken = PushNotificationToken::find()->where([
            'pollet_user_id' => $targetUserId,
        ])->one();
        if (!$targetToken) {
            return true;
        }
        
        $targetToken->device_id = $sourceToken->device_id;
        $targetToken->token     = $sourceToken->token;
        $targetToken->platform  = $sourceToken->platform;
        $targetToken->is_active = $sourceToken->is_active;

        return $targetToken->save();
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
    // platform status
    // ===============================================================

    /**
     * プラットフォームがiOSかどうかを返す
     * @return bool
     */
    public function isiOSUser()
    {
        return $this->platform === self::PLATFORM_IOS;
    }

    /**
     * プラットフォームがandroidかどうかを返す
     * @return bool
     */
    public function isAndroidUser()
    {
        return $this->platform === self::PLATFORM_ANDROID;
    }

    /**
     * iOS(APNS)用のデバイストークンを持っているかどうか
     * @return bool
     */
    public function hasiOSDeviceToken()
    {
        return ($this->hasDeviceToken() && $this->isiOSUser());
    }

    /**
     * android(GCM)用のデバイストークンを持っているかどうか
     * @return bool
     */
    public function hasAndroidDeviceToken()
    {
        return ($this->hasDeviceToken() && $this->isAndroidUser());
    }

    /**
     * デバイストークンを持っているかどうか
     * @return bool
     */
    public function hasDeviceToken()
    {
        return (strlen($this->token) > 0);
    }
}
