<?php

namespace app\models;

use Yii;
use yii\base\Model;

class FirstChargeRequest extends Model
{
    /**
     * 初回チャージ前のデータを処理待ち状態にする
     *
     * @param PolletUser $user
     *
     * @return bool
     */
    public function ready(PolletUser $user): bool
    {
        if (count($user->chargeRequestHistories) === 0) {
            // 何かおかしいがカード自体は発行されてしまっているのでログを出して許容する
            Yii::warning("チャージ申請履歴が存在しません。pollet_user_id: {$user->id}");

            return true;
        }

        $isSuccess = true;
        foreach ($user->chargeRequestHistories as $chargeRequest) {
            if ($chargeRequest->processing_status === ChargeRequestHistory::STATUS_UNPROCESSED_FIRST_CHARGE) {
                $chargeRequest->processing_status = ChargeRequestHistory::STATUS_READY;
                $isSuccess = $chargeRequest->save() && $isSuccess;
            }
        }

        return $isSuccess;
    }
}
