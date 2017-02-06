<?php
namespace app\models\charge_source_cooperation;

use app\models\ChargeSource;
use app\models\exceptions\ChargeSourceCooperation\CancelWithdrawalFailedException;
use app\models\exceptions\ChargeSourceCooperation\NotImplementedException;
use app\models\exceptions\PointSiteApiCooperation\RequestFailedException;
use app\models\PolletUser;
use yii\base\Model;

/**
 * @todo 提携タイプごとに同じインタフェースで扱う
 *
 * Class ChargeSourceCooperation
 * @package app\models\point_site_cooperation
 */
class ChargeSourceCooperation extends Model
{
    /**
     * 提携タイプに応じた引き落とし処理を行う
     *
     * @param ChargeSource $chargeSource
     * @param PolletUser $user
     * @param int $price
     * @param int $chargeRequestId
     * @return bool
     * @throws NotImplementedException
     */
    public static function withdrawCash(ChargeSource $chargeSource, PolletUser $user, int $price, int $chargeRequestId)
    {
        if ($chargeSource->cooperation_type === ChargeSource::COOPERATION_TYPE_POINT_SITE_API) {
            return PointSiteApiCooperation::exchange(
                $chargeSource->charge_source_code,
                $price,
                $user->id,
                $chargeRequestId
            );
        } else {
            throw new NotImplementedException('実装されていません');
        }
    }

    /**
     * 提携タイプに応じて有効なポイント数を取得する
     *
     * @param ChargeSource $chargeSource
     * @param PolletUser $user
     * @return int
     * @throws NotImplementedException
     */
    public static function getValidPointAsCash(ChargeSource $chargeSource, PolletUser $user)
    {
        if ($chargeSource->cooperation_type === ChargeSource::COOPERATION_TYPE_POINT_SITE_API) {
            return PointSiteApiCooperation::fetchValidPointAsCash($chargeSource->charge_source_code, $user->id);
        } else {
            throw new NotImplementedException('実装されていません');
        }
    }

    /**
     * 提携タイプに応じた引き落としキャンセル処理を行う
     *
     * @param ChargeSource $chargeSource
     * @param int $chargeRequestId
     * @throws NotImplementedException
     * @throws CancelWithdrawalFailedException
     */
    public static function cancelWithdrawal(ChargeSource $chargeSource, int $chargeRequestId)
    {
        if ($chargeSource->cooperation_type === ChargeSource::COOPERATION_TYPE_POINT_SITE_API) {
            try {
                PointSiteApiCooperation::cancelExchange($chargeSource->charge_source_code, $chargeRequestId);
            } catch (RequestFailedException $e) {
                throw new CancelWithdrawalFailedException($e->getMessage(), $e->getCode(), $e);
            }
        } else {
            throw new NotImplementedException('実装されていません');
        }
    }
}
