<?php

namespace app\models\queries;

use app\models\PushInformationOpening;
use yii\db\ActiveQuery;

/**
 * This is the ActiveQuery class for [[PushInformationOpening]].
 *
 * @see PushInformationOpening
 */
class PushInformationOpeningQuery extends ActiveQuery
{
    /**
     * @inheritdoc
     * @return PushInformationOpening[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return PushInformationOpening|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
