<?php

namespace app\models\queries;

use app\models\ChargeSource;
use app\models\PointSiteToken;
use yii\db\ActiveQuery;
use yii\db\Expression;

/**
 * This is the ActiveQuery class for [[ChargeSource]].
 *
 * @see ChargeSource
 */
class ChargeSourceQuery extends ActiveQuery
{
    /**
     * @return $this
     */
    public function active()
    {
        return $this->andWhere([
            ChargeSource::tableName(). '.publishing_status' => ChargeSource::PUBLISHING_STATUS_PUBLIC,
        ]);
    }

    /**
     * 連携タイプがアプリ完結型のものに絞る
     * @return $this
     */
    public function pointSiteApi()
    {
        return $this->andWhere([
            ChargeSource::tableName(). '.cooperation_type' => ChargeSource::COOPERATION_TYPE_POINT_SITE_API,
        ]);
    }

    /**
     * 初回チャージ時に選択できるものに絞る
     * @return $this
     */
    public function canChargeAtFirst()
    {
        return $this->andWhere([
            'in',
            ChargeSource::tableName(). '.cooperation_type',
            ChargeSource::typesCanChargeAtFirst(),
        ]);
    }

    /**
     * 承認が必要な提携タイプのみに絞る
     * @return $this
     */
    public function requireAuthorization()
    {
        return $this->andWhere([
            'in',
            ChargeSource::tableName(). '.cooperation_type',
            ChargeSource::typesRequireAuthorization(),
        ]);
    }

    /**
     * 承認が不要な提携タイプのみに絞る
     * @return $this
     */
    public function requireNoAuthorization()
    {
        return $this->andWhere([
            'not in',
            ChargeSource::tableName(). '.cooperation_type',
            ChargeSource::typesRequireAuthorization(),
        ]);
    }

    /**
     * 承認済みフラグを取得するために結合を行ったクエリを返す
     * @param bool $extractAuthorized 承認済みのものだけ抽出する
     * @return $this
     */
    public function joinAuthorized($extractAuthorized = false)
    {
        $site  = ChargeSource::tableName();
        $token = PointSiteToken::tableName();

        $query = $this->select([
            $site . '.*',
            $token . '.id IS NOT NULL AS `isAuthorized`',
        ])->leftJoin(
            $token,
            [
                $token . '.charge_source_code' => new Expression($site . '.charge_source_code'),
                $token . '.pollet_user_id' => \Yii::$app->user->id,
            ]
        );

        if ($extractAuthorized) {
            $query->andWhere(new Expression($token . '.id IS NOT NULL'));
        }

        return $query;
    }

    /**
     * @inheritdoc
     * @return ChargeSource[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return ChargeSource|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
