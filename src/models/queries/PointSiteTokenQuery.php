<?php

namespace app\models\queries;

use app\models\PointSiteToken;
use app\models\PolletUser;
use Yii;
use yii\db\ActiveQuery;

/**
 * This is the ActiveQuery class for [[PointSiteToken]].
 *
 * @see PointSiteToken
 */
class PointSiteTokenQuery extends ActiveQuery
{
    /**
     * @inheritdoc
     * @return PointSiteToken[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return PointSiteToken|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    /**
     * @return $this
     */
    public function mine()
    {
        /** @var PolletUser $identify */
        $identify = Yii::$app->user->identity;
        return $this->andWhere([
            PointSiteToken::tableName() . '.pollet_user_id' => $identify->id,
        ]);
    }

    /**
     * @param $pointSiteID integer 提携サイトID
     * @return $this
     */
    public function pointSiteOf($pointSiteID)
    {
//        $this->andWhere([
//            ChargeSource::tableName() . 'id' => $pointSiteID,
//        ]);
//        $this->joinWith([
//            'point_site',
//        ]);
        return $this;
    }
}
