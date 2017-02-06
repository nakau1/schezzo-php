<?php

namespace app\models\queries;

use app\models\InquiryReply;
use yii\db\ActiveQuery;

/**
 * This is the ActiveQuery class for [[InquiryReply]].
 *
 * @see InquiryReply
 */
class InquiryReplyQuery extends ActiveQuery
{
    /**
     * @inheritdoc
     * @return InquiryReply[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return InquiryReply|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
