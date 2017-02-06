<?php

namespace app\models\queries;

use app\helpers\Date;
use app\models\Reception;
use yii\db\ActiveQuery;

/**
 * This is the ActiveQuery class for [[Reception]].
 *
 * @see Reception
 */
class ReceptionQuery extends ActiveQuery
{
    /**
     * 受付IDで絞ったクエリを返す
     * @param $receptionId string 受付ID
     * @return $this
     */
    public function receptionId($receptionId)
    {
        return $this->andWhere([
            Reception::tableName(). '.reception_code' => $receptionId,
        ]);
    }

    /**
     * 受付IDで絞ったクエリを返す
     * @param $receptionIds string[] 受付ID
     * @return $this
     */
    public function receptionIds($receptionIds)
    {
        return $this->andWhere([
            Reception::tableName(). '.reception_code' => $receptionIds,
        ]);
    }

    /**
     * 有効期限内のものに絞ったクエリを返す
     * @param $date string|null 比較する日付(省略時は現在日付)
     * @return $this
     */
    public function active($date = null)
    {
        $date = $date ?? Date::now()->format(Date::DATETIME_FORMAT);
        return $this->andWhere([
            '>=',
            Reception::tableName() . '.expiry_date',
            $date,
        ]);
    }

    /**
     * ステータスで絞ったクエリを返す
     * @param $status string ステータス
     * @return $this
     */
    public function status($status)
    {
        return $this->andWhere([
            Reception::tableName(). '.reception_status' => $status,
        ]);
    }

    /**
     * 交換サイトコードで絞ったクエリを返す
     * @param $chargeSourceCode string 交換サイトコード
     * @return $this
     */
    public function chargeSourceCode($chargeSourceCode)
    {
        return $this->andWhere([
            Reception::tableName(). '.charge_source_code' => $chargeSourceCode,
        ]);
    }

    /**
     * @inheritdoc
     * @return Reception[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return Reception|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
