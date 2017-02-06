<?php

namespace app\models\queries;

use app\models\RegisterCampaignPointPercentage;
use yii\db\ActiveQuery;

/**
 * This is the ActiveQuery class for [[RegisterCampaignPointPercentage]].
 *
 * @see RegisterCampaignPointPercentage
 */
class RegisterCampaignPointPercentageQuery extends ActiveQuery
{
    /**
     * @inheritdoc
     * @return RegisterCampaignPointPercentage[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return RegisterCampaignPointPercentage|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
