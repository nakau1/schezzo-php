<?php
namespace app\models\queries;

/**
 * ActiveQuery 基底抽象クラス
 * Class ActiveQuery
 * @package app\models\queries
 */
abstract class ActiveQuery extends \yii\db\ActiveQuery
{
    /**
     * IDで絞る
     * @param int $id
     * @return $this
     */
    public function id($id)
    {
        return $this->andWhere([
            'id' => $id,
        ]);
    }
}
