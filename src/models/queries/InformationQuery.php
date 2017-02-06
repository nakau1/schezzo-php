<?php

namespace app\models\queries;

use app\models\Information;
use app\models\PushInformationOpening;
use yii\db\ActiveQuery;
use yii\db\Expression;

/**
 * This is the ActiveQuery class for [[Information]].
 *
 * @see Information
 */
class InformationQuery extends ActiveQuery
{
    /**
     * 公開期間内かつ公開中ステータスのものに絞ったクエリを返す
     * @return $this
     */
    public function published()
    {
        return $this->active()->inPublicTerm();
    }

    /**
     * 重要フラグの立つものに絞ったクエリを返す
     * @return $this
     */
    public function important()
    {
        return $this->andWhere([
            Information::tableName(). '.is_important' => 1,
        ]);
    }

    /**
     * 公開期間内のものに絞ったクエリを返す
     * @return $this
     */
    public function inPublicTerm()
    {
        $now = date('Y-m-d H:i:s');
        return $this->andWhere([
            '<=',
            Information::tableName() . '.begin_date',
            $now,
        ])->andWhere([
            '>=',
            Information::tableName() . '.end_date',
            $now,
        ]);
    }

    /**
     * 公開中ステータスのものに絞ったクエリを返す
     * @param string[] $statuses
     * @return $this
     */
    public function active($statuses = [Information::PUBLISHING_STATUS_PUBLIC])
    {
        return $this->andWhere([
            Information::tableName(). '.publishing_status' => $statuses,
        ]);
    }

    /**
     * 既読/未読のフラグを取得するために結合を行ったクエリを返す
     * @param bool $extractUnopened 未読のものだけ抽出する
     * @param int|null $userId 指定のユーザID(省略時は認証中のユーザ)
     * @return $this
     */
    public function joinOpening($extractUnopened = false, $userId = null)
    {
        $information = Information::tableName();
        $opening     = PushInformationOpening::tableName();

        $query = $this->select([
            $opening . '.id IS NOT NULL AS `isOpened`',
            $information. '.*',
        ])->leftJoin(
            $opening,
            [
                $opening. '.information_id' => new Expression($information. '.`id`'),
                $opening. '.pollet_user_id' => $userId ?? \Yii::$app->user->id,
            ]
        );

        if ($extractUnopened) {
            $query->andWhere(new Expression($opening . '.id IS NULL'));
        }

        return $query;
    }

    public function joinIsPublic()
    {
        $exp = new Expression('CASE WHEN '. Information::tableName() . ".publishing_status = '". Information::PUBLISHING_STATUS_PUBLIC ."' THEN 1 ELSE 0 END AS is_public");
        if ($this->select) {
            return $this->addSelect($exp);
        } else {
            return $this->select([Information::tableName() . '.*', $exp]);
        }
    }

    /**
     * @inheritdoc
     * @return Information[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return Information|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
