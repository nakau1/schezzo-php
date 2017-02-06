<?php

namespace app\models\queries;

use app\models\Inquiry;
use yii\db\ActiveQuery;

/**
 * This is the ActiveQuery class for [[Inquiry]].
 *
 * @see Inquiry
 */
class InquiryQuery extends ActiveQuery
{
    /**
     * @inheritdoc
     * @return Inquiry[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return Inquiry|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
