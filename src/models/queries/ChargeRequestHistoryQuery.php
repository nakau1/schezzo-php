<?php

namespace app\models\queries;

use app\helpers\YearMonth;
use app\models\ChargeRequestHistory;
use Yii;
use yii\db\ActiveQuery;

/**
 * This is the ActiveQuery class for [[ChargeRequestHistory]].
 *
 * @see ChargeRequestHistory
 */
class ChargeRequestHistoryQuery extends ActiveQuery
{
    /**
     * 認証中ユーザのものだけを抽出するクエリを返す
     * @return $this
     */
    public function mine()
    {
        return $this->andWhere([
            ChargeRequestHistory::tableName(). '.pollet_user_id' => Yii::$app->user->id,
        ]);
    }

    /**
     * 指定した年月のものだけを抽出するクエリを返す
     * @param string|null $month 'yymm'の形式の月(2016年9月は'1609')
     * @return $this
     */
    public function atMonth($month = null)
    {
        list($from, $to) = YearMonth::getDateStringsFromTo($month);
        return $this->andWhere([
            '>=',
            ChargeRequestHistory::tableName() . '.created_at',
            $from,
        ])->andWhere([
            '<=',
            ChargeRequestHistory::tableName() . '.created_at',
            $to,
        ]);
    }

    /**
     * 有効なものだけを抽出するクエリを返す
     * @return $this
     */
    public function active()
    {
        return $this->andWhere([
            'NOT IN',
            ChargeRequestHistory::tableName(). '.processing_status',
            [
                ChargeRequestHistory::STATUS_ERROR,
                ChargeRequestHistory::STATUS_ACCEPTED_RECEPTION,
                ChargeRequestHistory::STATUS_WAITING_APPLY,
            ],
        ]);
    }

    /**
     * チャージ適用されたものだけを抽出するクエリを返す
     * @return $this
     */
    public function applied()
    {
        return $this->andWhere([
            ChargeRequestHistory::tableName(). '.processing_status' => ChargeRequestHistory::STATUS_APPLIED_CHARGE,
        ]);
    }

    /**
     * 有効かつチャージ適用されていないものだけを抽出するクエリを返す
     * @return $this
     */
    public function unapplied()
    {
        return $this->andWhere([
            ChargeRequestHistory::tableName(). '.processing_status' => [
                ChargeRequestHistory::STATUS_UNPROCESSED_FIRST_CHARGE,
                ChargeRequestHistory::STATUS_READY,
                ChargeRequestHistory::STATUS_IS_MAKING_PAYMENT_FILE,
                ChargeRequestHistory::STATUS_MADE_PAYMENT_FILE,
                ChargeRequestHistory::STATUS_REQUESTED_CHARGE,
            ]
        ]);
    }

    /**
     * @inheritdoc
     * @return ChargeRequestHistory[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return ChargeRequestHistory|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
