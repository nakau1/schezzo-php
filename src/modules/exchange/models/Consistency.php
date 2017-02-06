<?php
namespace app\modules\exchange\models;

use app\models\ChargeRequestHistory;
use app\models\PolletUser;
use yii\base\Model;
use Yii;

/**
 * Class Consistency
 * @package app\modules\exchange\models
 *
 * @property PolletUser $user
 * @property Reception $reception
 */
class Consistency extends Model
{
    public $reception_id;

    public $user;
    public $reception;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['reception_id'], 'string'],
            [['reception_id'], 'required'],
        ];
    }

    /**
     * ユーザ整合性チェックとステータス更新処理
     * @return bool
     */
    public function check()
    {
        if (!$this->validate()) {
            return false;
        }

        // 受付の検索
        $reception = $this->findReception();
        if (!$reception) {
            $this->addError('reception_id', "指定した受付が見つかりません");
            return false;
        }

        // ユーザの整合性チェック
        if (!$this->checkUserConsistent($reception)) {
            $this->addError('reception_id', 'ユーザとの整合性が取れません');
            return false;
        }
        $this->reception = $reception;
        return true;
    }

    /**
     * 各ステータスを更新する
     * 先にcheck()で該当の受付をチェックしておく必要あり
     * @return bool
     */
    public function updateStatuses()
    {
        if (!$this->reception) {
            $this->addError('reception_id', "指定した受付が見つかりません");
            return false;
        }

        // データ更新
        $transaction = Yii::$app->db->beginTransaction();
        try {
            // 受付APIのみをキックされている状態
            if ($this->reception->isAccepted()) {
                // 申請API待ち状態に変更
                $this->reception->chargeRequestHistory->processing_status = ChargeRequestHistory::STATUS_WAITING_APPLY;
                if (!$this->reception->chargeRequestHistory->save()) {
                    throw new \Exception();
                }
            }
            // 受付API => 申請APIの順でキックされている状態
            elseif ($this->reception->isApplied()) {
                // チャージ処理待ちに変更(初回チャージの場合は初回チャージ未処理に変更)
                $status = $this->user->isFirstChargeProcessing() ?
                    ChargeRequestHistory::STATUS_UNPROCESSED_FIRST_CHARGE :
                    ChargeRequestHistory::STATUS_READY;
                $this->reception->chargeRequestHistory->processing_status = $status;
                if (!$this->reception->chargeRequestHistory->save()) {
                    throw new \Exception();
                }
                // 受付を申請済に変更
                $this->reception->reception_status = Reception::RECEPTION_STATUS_APPLIED;
                if (!$this->reception->save()) {
                    throw new \Exception();
                }
            }

            // 初回チャージ中のユーザは「初回チャージ申請完了済」に更新される
            if ($this->user->isFirstChargeProcessing()) {
                $this->user->registration_status = PolletUser::STATUS_CHARGE_REQUESTED;
                if (!$this->user->save()) {
                    throw new \Exception();
                }
            }

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            $this->addError('reception_id', "保存に失敗しました");
            return false;
        }
        return true;
    }

    /**
     * 受付の検索
     * アプリのユーザと、受付IDに紐付くユーザが同じかどうかの確認をする
     * @return Reception|array|null
     */
    private function findReception() {
        return Reception::find()
            ->active()
            ->receptionId($this->reception_id)
            ->one();
    }

    /**
     * ユーザの整合性チェック
     * アプリのユーザと、受付IDに紐付くユーザが同じかどうかの確認をする
     * @param $reception Reception 受付モデル
     * @return bool 整合性が合えばtrue
     */
    private function checkUserConsistent($reception) {
        if (!$this->user) {
            return false;
        }
        return ($this->user->id === $reception->pollet_user_id);
    }

    /**
     * @inheritdoc
     */
    public function formName()
    {
        return '';
    }
}