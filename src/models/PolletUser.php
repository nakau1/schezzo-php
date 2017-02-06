<?php

namespace app\models;

use app\components\CedynaMyPageWithCache;
use app\components\Crypt;
use app\models\queries\PolletUserQuery;
use DomainException;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "pollet_user".
 *
 * @property integer                      $id
 * @property string                       $user_code_secret
 * @property string                       $cedyna_id
 * @property string                       $encrypted_password
 * @property string                       $mail_address
 * @property string                       $registration_status
 * @property integer                      $balance_at_charge      チャージ時の残高
 * @property string                       $updated_at
 * @property string                       $created_at
 *
 * @property CardValueCache               $cardValueCache
 * @property ChargeRequestHistory[]       $chargeRequestHistories
 * @property Inquiry[]                    $inquiries
 * @property MonthlyTradingHistoryCache[] $monthlyTradingHistoryCaches
 * @property PointSiteToken[]             $pointSiteTokens
 * @property ChargeSource[]               $chargeSources
 * @property PushInformationOpening[]     $pushInformationOpenings
 * @property PushNotificationToken[]      $pushNotificationTokens
 *
 * @property int|boolean                  $myChargedValue         ユーザーの状態に応じたチャージ残高
 * @property integer                      $unreadInformationCount ユーザに未読お知らせ数
 * @property boolean                      $hasUnreadInformation   ユーザに未読お知らせがあるかどうか
 * @property string                       $rawPassword            平文のパスワード
 */
class PolletUser extends ActiveRecord implements IdentityInterface
{
    const STATUS_NEW_USER           = 'new_user';           // 新規ユーザ
    const STATUS_SITE_AUTHENTICATED = 'site_authenticated'; // 初回サイト認証完了済
    const STATUS_CHARGE_REQUESTED   = 'charge_requested';   // 初回チャージ申請完了済
    const STATUS_WAITING_ISSUE      = 'waiting_issue';      // 発番待ち
    const STATUS_ISSUED             = 'issued';             // 発番済
    const STATUS_ACTIVATED          = 'activated';          // アクティベート完了
    const STATUS_SIGN_OUT           = 'sign-out';           // ログアウト済み(アクティベートはされている)
    const STATUS_REMOVED            = 'removed';            // 削除済み

    const SCENARIO_USER_CODE_NULLABLE = 'nullable user_code_secret';

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
    public static function tableName()
    {
        return 'pollet_user';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_code_secret', 'registration_status'], 'required', 'on' => [self::SCENARIO_DEFAULT]],
            [['registration_status'], 'required', 'on' => [self::SCENARIO_USER_CODE_NULLABLE]], // user_code_secret は requiredから外す
            [['cedyna_id', 'balance_at_charge'], 'integer'],
            [['cedyna_id', 'balance_at_charge'], 'match', 'pattern' => '/\A[0-9]{1,16}\z/u'],
            [['encrypted_password'], 'string'],
            [['user_code_secret'], 'string', 'max' => 64],
            [['mail_address'], 'string', 'max' => 256],
            [['registration_status'], 'string', 'max' => 35],
            [['user_code_secret'], 'unique'],
            [['cedyna_id'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'                  => 'ID',
            'user_code_secret'    => 'ユーザコード',
            'cedyna_id'           => 'セディナID',
            'encrypted_password'  => '暗号化パスワード',
            'mail_address'        => 'メールアドレス',
            'registration_status' => 'ステータス(登録状態)',
            'balance_at_charge'   => 'チャージ時の残高',
            'updated_at'          => '更新日時',
            'created_at'          => '作成日時',
        ];
    }

    // ===============================================================
    // relations
    // ===============================================================

    /**
     * @return ActiveQuery
     */
    public function getCardValueCache()
    {
        return $this->hasOne(CardValueCache::className(), ['pollet_user_id' => 'id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getChargeRequestHistories()
    {
        return $this->hasMany(ChargeRequestHistory::className(), ['pollet_user_id' => 'id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getInquiries()
    {
        return $this->hasMany(Inquiry::className(), ['pollet_user_id' => 'id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getMonthlyTradingHistoryCaches()
    {
        return $this->hasMany(MonthlyTradingHistoryCache::className(), ['pollet_user_id' => 'id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getPointSiteTokens()
    {
        return $this->hasMany(PointSiteToken::className(), ['pollet_user_id' => 'id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getChargeSources()
    {
        return $this->hasMany(ChargeSource::className(),
            ['charge_source_code' => 'charge_source_code'])->viaTable('point_site_token',
            ['pollet_user_id' => 'id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getPushInformationOpenings()
    {
        return $this->hasMany(PushInformationOpening::className(), ['pollet_user_id' => 'id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getPushNotificationTokens()
    {
        return $this->hasMany(PushNotificationToken::className(), ['pollet_user_id' => 'id']);
    }

    // ===============================================================
    // find
    // ===============================================================

    /**
     * @inheritdoc
     * @return PolletUserQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new PolletUserQuery(get_called_class());
    }

    // ===============================================================
    // save
    // ===============================================================

    /**
     * ユーザのステータスを更新する
     * @param $status string 変更後のステータス
     * @return bool 成功/失敗
     */
    public function updateStatus($status)
    {
        $trans = Yii::$app->db->beginTransaction();
        try {
            $this->registration_status = $status;

            if (!$this->save()) {
                throw new \Exception('failed change status.');
            }
            $trans->commit();
            return true;
        } catch (\Exception $e) {
            $trans->rollBack();
            return false;
        }
    }

    /**
     * ユーザのトークンを更新する
     * @param $newUserCodeSecret string 新しいトークン
     * @return bool 成功/失敗
     */
    public function updateUserCodeSecret($newUserCodeSecret)
    {
        $trans = Yii::$app->db->beginTransaction();
        try {
            $this->user_code_secret = $newUserCodeSecret;

            if (!$this->save()) {
                throw new \Exception('failed update user-token-secret.');
            }
            $trans->commit();
            return true;
        } catch (\Exception $e) {
            $trans->rollBack();
            return false;
        }
    }

    /**
     * ユーザのパスワードを更新する
     * @param $newPassword string 新しいパスワード
     * @return bool 成功/失敗
     */
    public function updatePassword($newPassword)
    {
        $trans = Yii::$app->db->beginTransaction();
        try {
            $this->rawPassword = $newPassword;

            if (!$this->save()) {
                throw new \Exception('failed update password.');
            }
            $trans->commit();
            return true;
        } catch (\Exception $e) {
            $trans->rollBack();
            return false;
        }
    }

    /**
     * ユーザを削除する
     * @return bool 成功/失敗
     */
    public function requestRemove()
    {
        $trans = Yii::$app->db->beginTransaction();
        try {
            $this->registration_status = self::STATUS_REMOVED;

            if (!$this->save()) {
                throw new \Exception('failed remove user.');
            }
            $trans->commit();
            return true;
        } catch (\Exception $e) {
            $trans->rollBack();
            return false;
        }
    }

    // ===============================================================
    // alias method for status
    // ===============================================================

    /**
     * 新規ユーザかどうかを返す
     * @return bool
     */
    public function isNewUser()
    {
        return $this->registration_status === self::STATUS_NEW_USER;
    }

    /**
     * 初回サイト認証完了済のユーザかどうかを返す
     * @return bool
     */
    public function isSiteAuthenticated()
    {
        return $this->registration_status === self::STATUS_SITE_AUTHENTICATED;
    }

    /**
     *  初回チャージ申請完了済のユーザかどうかを返す
     * @return bool
     */
    public function isChargeRequested()
    {
        return $this->registration_status === self::STATUS_CHARGE_REQUESTED;
    }

    /**
     * 発番待ちのユーザかどうかを返す
     * @return bool
     */
    public function isWaitingIssue()
    {
        return $this->registration_status === self::STATUS_WAITING_ISSUE;
    }

    /**
     * 発番済のユーザかどうかを返す
     * @return bool
     */
    public function isIssued()
    {
        return $this->registration_status === self::STATUS_ISSUED;
    }

    /**
     * アクティベート完了状態のユーザかどうかを返す
     * @return bool
     */
    public function isActivatedUser()
    {
        return $this->registration_status === self::STATUS_ACTIVATED;
    }

    /**
     * ログアウト状態のユーザかどうかを返す
     * @return bool
     */
    public function isSignOut()
    {
        return $this->registration_status === self::STATUS_SIGN_OUT;
    }

    /**
     * 初回チャージの処理中かどうか(新規ユーザ〜初回チャージ申請完了済かどうか)の判定
     * @return bool 初回チャージの処理中かどうか
     */
    public function isFirstChargeProcessing()
    {
        return in_array($this->registration_status, self::getFirstChargeProcessingStatuses());
    }

    /**
     * 初回チャージの処理中のステータスを配列で返す
     * ここでいう「初回チャージの処理中」はセディナのカードが発行される前のステータスであることを指す
     * @return string[] 初回チャージの処理中のステータス
     */
    public static function getFirstChargeProcessingStatuses()
    {
        return [
            self::STATUS_NEW_USER,
            self::STATUS_SITE_AUTHENTICATED,
            self::STATUS_CHARGE_REQUESTED,
            self::STATUS_WAITING_ISSUE,
        ];
    }

    // ===============================================================
    // dynamic getter/setter properties
    // ===============================================================

    /**
     * @param string|null $rawPassword
     */
    public function setRawPassword($rawPassword)
    {
        if (is_null($rawPassword)) {
            $this->encrypted_password = null;
            return;
        }

        if (is_null($this->cedyna_id)) {
            throw new DomainException('セディナIDの設定が必要です');
        }
        $crypt = new Crypt();
        $this->encrypted_password = $crypt->encrypt($rawPassword, $this->cedyna_id);
    }

    /**
     * @return string|null
     */
    public function getRawPassword()
    {
        if (is_null($this->encrypted_password)) {
            return null;
        }

        if (is_null($this->cedyna_id)) {
            throw new DomainException('セディナIDの設定が必要です');
        }
        $crypt = new Crypt();
        return $crypt->decrypt($this->encrypted_password, $this->cedyna_id);

    }

    /**
     * ユーザに未読お知らせ件数
     * @return integer
     */
    public function getUnreadInformationCount()
    {
        return (int)Information::find()->joinOpening(true, $this->id)->published()->important()->count();
    }

    /**
     * ユーザに未読お知らせがあるかどうかを返す
     * @return bool
     */
    public function getHasUnreadInformation()
    {
        return ($this->unreadInformationCount > 0);
    }

    /**
     * ユーザーの状態に応じたチャージ残高を返す
     * @return bool|int
     */
    public function getMyChargedValue()
    {
        switch ($this->registration_status) {
            case self::STATUS_CHARGE_REQUESTED:
            case self::STATUS_WAITING_ISSUE:
            case self::STATUS_ISSUED:
                return ChargeRequestHistory::find()->mine()->active()->sum('charge_value') ?? 0;
            case self::STATUS_ACTIVATED:
                $cedynaWithCache = CedynaMyPageWithCache::getInstance();
                $cardValue = $cedynaWithCache->cardValueCache($this->cedyna_id);
                if ($cardValue === null) {
                    if (!$cedynaWithCache->login($this->cedyna_id, $this->rawPassword)) {
                        // ログイン失敗
                        return false;
                    }
                    $cardValue = $cedynaWithCache->cardValue();
                }
                return $cardValue;
            default:
                return 0;
        }
    }

    // ===============================================================
    // implementation for IdentityInterface
    // ===============================================================

    /**
     * @param int|string $id
     * @return PolletUser|array|null
     */
    public static function findIdentity($id)
    {
        return self::find()->andWhere([
            self::tableName() . '.id' => $id,
        ])->one();
    }

    /**
     * @inheritdoc
     * @param mixed $token
     * @param null  $type
     * @return PolletUser|array|null
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne([
            'user_code_secret' => $token,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->id == $authKey;
    }
}
