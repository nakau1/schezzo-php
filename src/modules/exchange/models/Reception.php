<?php
namespace app\modules\exchange\models;

use app\components\GeneralChargeBonus;
use app\helpers\Date;
use app\models\ChargeRequestHistory;
use app\models\PolletUser;
use app\modules\exchange\helpers\Messages;
use Yii;

/**
 * Class Reception
 * @package app\modules\exchange\models
 *
 * @property SiteAuthorize $siteAuthorize
 */
class Reception extends \app\models\Reception
{
    const SCENARIO_API_REQUEST = 'api-request';

    public $card_number;
    public $amount;
    public $delay;

    public $siteAuthorize;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        if ($this->scenario === self::SCENARIO_API_REQUEST) {
            return [
                [['pollet_user_id', 'card_number'], 'integer'],
                [['delay'], 'integer', 'integerPattern' => '/[0-1]{1}/', 'message' => Messages::INVALID_PARAM],

                [['amount'], 'required', 'message' => Messages::REQUIRED_EMPTY],
                [['amount'],
                    'integer',
                    'min'      => $this->siteAuthorize->getChargeSource()->min_value,
                    'max'      => self::MAX_PRICE_PER_CHARGE,
                    'tooSmall' => Messages::AMOUNT_RANNGE_OUT,
                    'tooBig'   => Messages::AMOUNT_RANNGE_OUT,
                ],

                [['charge_source_code'], 'required', 'message' => Messages::ERR_UNAUTHORIZED],
            ];
        } else {
            return parent::rules();
        }
    }

    /**
     * 受付を受理する
     */
    public function accept()
    {
        if (!$this->validate()) {
            return false;
        }

        $user = $this->checkUser();
        if (!$user) {
            if (is_null($user)) {
                $this->addError('id', Messages::USER_NOT_FOUND);
            }
            return false;
        }

        $chargeSource = $this->siteAuthorize->getChargeSource();
        // 初回チャージ時のみカード発行手数料がかかる
        $cardIssueFee = $this->isFirstChargingUser($user) ? $chargeSource->card_issue_fee : 0;
        $generalChargeBonus = new GeneralChargeBonus();
        $chargePrice = $generalChargeBonus->applyTo($this->amount) - $cardIssueFee;

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $chargeRequest = ChargeRequestHistory::add(
                $chargeSource,
                $user,
                $chargePrice,  // チャージする額
                $this->amount, // ポイントサイトから差し引く額
                ChargeRequestHistory::STATUS_ACCEPTED_RECEPTION
            );

            $this->reception_code   = Yii::$app->security->generateRandomString();
            $this->reception_status = self::RECEPTION_STATUS_ACCEPTED;
            $this->pollet_user_id   = $user->id;
            $this->expiry_date      = $this->generateExpiryDate();
            $this->charge_value     = $this->amount;
            $this->charge_request_history_id = $chargeRequest->id;

            if (!$this->save()) {
                throw new \Exception();
            }
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            $this->addError('id');
            return false;
        }

        return true;
    }

    /**
     * @param PolletUser $user
     * @return bool
     */
    private function isFirstChargingUser(PolletUser $user)
    {
        return $user->isNewUser() || $user->isSiteAuthenticated();
    }

    /**
     * @return PolletUser|bool
     */
    private function checkUser()
    {
        if ($this->card_number) {
            $this->by_card_number = true;
            return PolletUser::find()->cedynaId($this->card_number)->one();
        } else if ($this->pollet_user_id) {
            return PolletUser::findIdentity($this->pollet_user_id);
        } else {
            $this->addError('id', Messages::REQUIRED_EMPTY);
            return false;
        }
    }

    /**
     * 有効期限をdelayフラグによって分岐して返す
     * @return string DB用有効期限文字列
     */
    private function generateExpiryDate()
    {
        $expiry = ($this->delay == 1) ?
            Date::now()->addDays(10) :  // 10日間
            Date::now()->addMinutes(5); // 5分間
        return $expiry->format(Date::DATETIME_FORMAT);
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        return array_merge(parent::scenarios(), [
            self::SCENARIO_API_REQUEST  => [
                'card_number',
                'pollet_user_id',
                'amount',
                'delay',
                'charge_source_code',
            ],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function formName()
    {
        return '';
    }

    /**
     * DBのタイムスタンプ値をAPIの戻り値用に変換
     * @param $timestamp
     * @return false|string
     */
    public function convertDateTime($timestamp)
    {
        return Date::createFromFormat(Date::DATETIME_FORMAT, $timestamp)->format('D M d H:i:s Y O');
    }
}