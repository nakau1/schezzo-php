<?php

namespace app\models\forms;

use Yii;
use app\models\Inquiry;

/**
 * 問い合わせフォームモデルクラス
 * @package app\models\forms
 */
class InquiryForm extends Inquiry
{
    /**
     * 問い合わせを送信する
     * @param integer|null $polletUserId ポレットユーザID
     * @return bool 成功/失敗
     */
    public function contact($polletUserId)
    {
        $this->pollet_user_id = $polletUserId;

        if ($this->validate()) {
            $trans = Yii::$app->db->beginTransaction();
            try {
                if (!$this->save()) {
                    throw new \Exception('failed add inquiry.');
                }
                $trans->commit();
                return true;
            } catch (\Exception $e) {
                $trans->rollBack();
                $this->addError('content', 'エラーが発生しました');
                return false;
            }
        }
        return false;
    }
}
