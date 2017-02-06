<?php
namespace app\modules\exchange\models;

use app\helpers\Date;
use app\models\ChargeRequestHistory;
use app\models\PolletUser;
use app\modules\exchange\helpers\Messages;
use yii\base\Model;
use yii\db\Expression;
use Yii;

/**
 * Class Reception
 * @package app\modules\exchange\models
 *
 * @property SiteAuthorize $siteAuthorize
 */
class ApplyInquire extends Model
{
    // 一度に処理できる受付(ID)の数
    const MAX_RECEPTION_IDS = 100;

    const TEMP_TABLE = "requested_reception_ids";

    public $reception_ids;

    public $siteAuthorize;

    private $results = [];

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['reception_ids'], 'string', 'message' => Messages::INVALID_PARAM],
        ];
    }

    /**
     * 渡した受付IDのすべてに申請を受理させて
     * APIで返すためのデータ結果をオブジェクトに保持させる
     */
    public function apply()
    {
        if (!$this->validate()) {
            return false;
        }

        $ids = explode(',', $this->reception_ids);
        if (!strlen($this->reception_ids)) {
            // IDが空の場合はエラーではないのでtrueを返す
            return true;
        } else if (!$this->checkReceptionIds($ids)) {
            // IDが多すぎる場合はエラー
            return false;
        }

        // ステータスを更新
        $this->updateStatuses($ids);

        // 問い合わせ結果を得る
        $this->queryInquireData($ids, $this->siteAuthorize->getChargeSource()->charge_source_code);
        return true;
    }

    /**
     * ステータスを更新する
     * @param $ids string[] 受付IDの配列
     * @return bool 成功/失敗
     */
    private function updateStatuses($ids)
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $now = Date::now()->format(Date::DATETIME_FORMAT);
            $this->updateReceptionStatusToApplied($ids, $now);
            $this->updateChargeRequestHistoryStatusToReady($ids, $now);
            $this->updateChargeRequestHistoryStatusToUnprocessedFirstCharge($ids, $now);
            $this->updateReceptionsByCardNumberToChargeReady($ids, $now);
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            $this->addError('id', '');
            return false;
        }
        return true;
    }

    /**
     * 対象の受付レコードのステータスを申請済に変更する
     * @param $ids string[] 受付IDの配列
     * @param $now string 現在文字列
     */
    private function updateReceptionStatusToApplied($ids, $now)
    {
        $receptions = Reception::find()
            ->active($now)
            ->status(Reception::RECEPTION_STATUS_ACCEPTED)
            ->chargeSourceCode($this->siteAuthorize->getChargeSource()->charge_source_code)
            ->receptionIds($ids)
            ->all();

        $filteredIds = array_map(function(Reception $reception) {
            return $reception->id;
        }, $receptions);

        Reception::updateAll([
            Reception::tableName() .'.reception_status' => Reception::RECEPTION_STATUS_APPLIED,
        ], [
            Reception::tableName() .'.id' => $filteredIds,
        ]);
    }

    /**
     * 対象のチャージ申請履歴処理レコードのステータスを準備済に変更する
     * (初回チャージ処理中ではないレコードのみ更新する)
     * @param $ids string[] 受付IDの配列
     * @param $now string 現在時刻文字列
     */
    private function updateChargeRequestHistoryStatusToReady($ids, $now)
    {
        $this->updateChargeRequestHistoryStatus($ids, $now, ChargeRequestHistory::STATUS_READY, false);
    }

    /**
     * 対象のチャージ申請履歴処理レコードのステータスを"unprocessed_first_charge"に変更する
     * (初回チャージ処理中のレコードのみ更新する)
     * @param $ids string[] 受付IDの配列
     * @param $now string 現在時刻文字列
     */
    private function updateChargeRequestHistoryStatusToUnprocessedFirstCharge($ids, $now)
    {
        $this->updateChargeRequestHistoryStatus($ids, $now, ChargeRequestHistory::STATUS_UNPROCESSED_FIRST_CHARGE, true);
    }

    /**
     * 対象のチャージ申請履歴レコードの処理ステータスを変更する共通処理
     * @param $ids string[] 受付IDの配列
     * @param $now string 現在時刻文字列
     * @param $processing_status string 変更後のステータス
     * @param $in bool 初回チャージ処理中のステータスに絞るかどうか true: "IN", false: "NOT IN"
     */
    private function updateChargeRequestHistoryStatus($ids, $now, $processing_status, $in)
    {
        $charge = ChargeRequestHistory::tableName();
        $user   = PolletUser::tableName();

        $receptions = Reception::find()
            ->active($now)
            ->status(Reception::RECEPTION_STATUS_APPLIED)
            ->chargeSourceCode($this->siteAuthorize->getChargeSource()->charge_source_code)
            ->receptionIds($ids)
            ->innerJoin($charge, [
                Reception::tableName().'.charge_request_history_id' => new Expression($charge.'.id'),
            ])
            ->innerJoin($user, [
                Reception::tableName().'.pollet_user_id' => new Expression($user.'.id'),
            ])
            ->andWhere([
                $charge. '.processing_status' => ChargeRequestHistory::STATUS_WAITING_APPLY,
            ])
            ->andWhere([
                ($in ? 'in' : 'not in'),
                'registration_status',
                PolletUser::getFirstChargeProcessingStatuses(),
            ])
            ->all();

        $filteredIds = array_map(function(Reception $reception) {
            return $reception->charge_request_history_id;
        }, $receptions);

        ChargeRequestHistory::updateAll([
            $charge .'.processing_status' => $processing_status,
        ], [
            $charge .'.id' => $filteredIds,
        ]);
    }

    /**
     * カード会員番号からユーザと紐付けた受付すべてを、チャージ準備済み状態に変更する
     * @param $ids string[] 受付IDの配列
     * @param $now string 現在時刻文字列
     */
    private function updateReceptionsByCardNumberToChargeReady($ids, $now)
    {
        $receptions = Reception::find()
            ->active($now)
            ->status(Reception::RECEPTION_STATUS_APPLIED)
            ->chargeSourceCode($this->siteAuthorize->getChargeSource()->charge_source_code)
            ->receptionIds($ids)
            ->andWhere([
                Reception::tableName(). '.by_card_number' => true,
            ])
            ->all();

        // 各レコードに"ユーザ整合性チェック"と同じ処理を行う
        foreach ($receptions as $reception) {
            $consistency = new Consistency();
            $consistency->user = $reception->polletUser;
            $params = [
                'reception_id' => $reception->reception_code,
            ];
            if (!$consistency->load($params) || !$consistency->check() || !$consistency->updateStatuses()) {
                $this->addError('reception_ids', '');
                return;
            }
        }
    }

    /**
     * 渡した受付IDをすべて問い合わせて
     * APIで返すためのデータ結果をオブジェクトに保持させる
     */
    public function inquire()
    {
        if (!$this->validate()) {
            return false;
        }

        $ids = explode(',', $this->reception_ids);
        if (!strlen($this->reception_ids)) {
            // IDが空の場合はエラーではないのでtrueを返す
            return true;
        } else if (!$this->checkReceptionIds($ids)) {
            // IDが多すぎる場合はエラー
            return false;
        }

        // 問い合わせ結果を得る
        $this->queryInquireData($ids, $this->siteAuthorize->getChargeSource()->charge_source_code);
        return true;
    }

    /**
     * 問い合わせた結果を取得する
     * @return array (受付ID, 金額, ステータス)のデータ
     */
    public function getResults()
    {
        return $this->results;
    }

    /**
     * 渡されたIDの数を検査する
     * @param array $ids IDの配列
     * @return bool 検査結果
     */
    private function checkReceptionIds(array $ids)
    {
        if (count($ids) > self::MAX_RECEPTION_IDS) {
            $this->addError('reception_ids', Messages::TOO_MANY_IDS);
            return false;
        }
        return true;
    }

    /**
     * 渡したIDのデータ(受付ID, 金額, ステータス)をすべて取得する
     * @param $ids string[] 受付IDの配列
     * @param $charge_source_code string 交換サイトコード
     */
    private function queryInquireData($ids, $charge_source_code)
    {
        $query = Reception::find()
            ->select([
                self::TEMP_TABLE. '.reception_id',
                Reception::tableName(). '.charge_value AS amount',
                new Expression($this->makeReceptionStatusCaseSQL()),
            ])
            ->from(
                new Expression($this->makeRequestedReceptionIdsTableSQL($ids))
            )
            ->leftJoin($this->makeFilteredReceptionSubQuery($charge_source_code), [
                self::TEMP_TABLE.'.reception_id' => new Expression(Reception::tableName().'.reception_code'),
            ]);

        $command = $query->createCommand();
        $this->results = $command->queryAll();

        /* 作成されるSQLの例(3つのIDを問い合わせたとき)
        ---------------------------------------------------------------
        SELECT
          `requested_reception_ids`.`reception_id`,
          `reception`.`charge_value` AS `amount`,
          CASE
            WHEN reception.reception_status IS NULL THEN 'unknown'
            WHEN reception.expiry_date < '2016-12-19 08:03:50' THEN 'expired'
            ELSE reception.reception_status
          END AS reception_status

        FROM
        (
            SELECT '3C4yNKcP4rbqRYScMqgKwbSx4Wo0VaM3' AS reception_id
            UNION ALL
            SELECT 'zcWAeRKZN4J-nruqrrFqVRX1nqLwSzn_' AS reception_id
            UNION ALL
            SELECT 'N4J7Uv_P-riZQq7qU6yL0vTnBGbUSCiw' AS reception_id
        ) AS requested_reception_ids

        LEFT JOIN
            (SELECT * FROM `reception` WHERE `reception`.`charge_source_code`='netmile') AS reception
            ON `requested_reception_ids`.`reception_id`=reception.reception_code
        */
    }

    /**
     * queryInquireData()の受付ステータスを取得するためのCASE文を作成する
     * - ユーザが見つからない場合は unkonwn
     * - 有効期限が切れている場合は expired
     * - それ以外はレコードのステータス
     * @return string SQL文
     */
    private function makeReceptionStatusCaseSQL()
    {
        $now = Date::now()->format(Date::DATETIME_FORMAT);
        $sql  = "CASE";
        $sql .= " WHEN ". Reception::tableName() .".reception_status IS NULL THEN '". Reception::RECEPTION_STATUS_UNKNOWN . "'";
        $sql .= " WHEN ". Reception::tableName() .".expiry_date < '". $now ."' THEN '". Reception::RECEPTION_STATUS_EXPIRED. "'";
        $sql .= " ELSE ". Reception::tableName() .".reception_status";
        $sql .= " END";
        return "$sql AS reception_status";
    }

    /**
     * 受付IDのみの結合用仮テーブル用SQL文を作成する
     * @param $ids string[] 受付IDの配列
     * @return string SQL文
     */
    private function makeRequestedReceptionIdsTableSQL($ids) {
        $queries = [];
        foreach ($ids as $id) {
            $escapedId = Yii::$app->db->quoteValue($id);
            $queries[] = "SELECT $escapedId AS reception_id";
        }
        $sql = implode(' UNION ALL ', $queries);
        return "($sql) AS requested_reception_ids";
    }

    /**
     * receptionテーブルから指定の交換サイトのみを抽出したサブクエリ用のSQL文を取得する
     * @param $charge_source_code string 交換サイトコード
     * @return string SQL文
     */
    private function makeFilteredReceptionSubQuery($charge_source_code) {
        $query = Reception::find()->andWhere([
            Reception::tableName(). '.charge_source_code' => $charge_source_code,
        ]);
        $sql = $query->createCommand()->rawSql;
        return "($sql) AS ". Reception::tableName();
    }

    /**
     * @inheritdoc
     */
    public function formName()
    {
        return '';
    }
}