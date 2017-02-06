<?php
namespace tests\unit\fixtures;

use app\models\ChargeRequestHistory;
use app\models\ChargeSource;
use app\models\PolletUser;
use Faker;

class ReceiveNumberedCedynaIdFixture extends PolletDbFixture
{
    public function load()
    {
        // 共通で使うチャージ元
        $chargeSource = $this->createChargeSource();

        // 通常の更新対象ユーザー
        $user = $this->createUser(10001, PolletUser::STATUS_WAITING_ISSUE);
        $this->createChargeRequest(100001, ChargeRequestHistory::STATUS_UNPROCESSED_FIRST_CHARGE, $user, $chargeSource);

        // 通常の更新対象ユーザー2
        $user = $this->createUser(10002, PolletUser::STATUS_WAITING_ISSUE);
        $this->createChargeRequest(100002, ChargeRequestHistory::STATUS_UNPROCESSED_FIRST_CHARGE, $user, $chargeSource);

        // リトライ対象ユーザー
        $user = $this->createUser(10003, PolletUser::STATUS_WAITING_ISSUE);
        $this->createChargeRequest(100003, ChargeRequestHistory::STATUS_UNPROCESSED_FIRST_CHARGE, $user, $chargeSource);

        // リトライ対象ユーザー2
        $user = $this->createUser(10004, PolletUser::STATUS_WAITING_ISSUE);
        $this->createChargeRequest(100004, ChargeRequestHistory::STATUS_UNPROCESSED_FIRST_CHARGE, $user, $chargeSource);

        // ヘッダ行に存在する polletId (処理しない)
        $user = $this->createUser(10005, PolletUser::STATUS_WAITING_ISSUE);
        $this->createChargeRequest(100005, ChargeRequestHistory::STATUS_UNPROCESSED_FIRST_CHARGE, $user, $chargeSource);

        // データ行に存在する polletId (処理する)
        $user = $this->createUser(10006, PolletUser::STATUS_WAITING_ISSUE);
        $this->createChargeRequest(100006, ChargeRequestHistory::STATUS_UNPROCESSED_FIRST_CHARGE, $user, $chargeSource);

        // トレーラ行に存在する polletId (処理しない)
        $user = $this->createUser(10007, PolletUser::STATUS_WAITING_ISSUE);
        $this->createChargeRequest(100007, ChargeRequestHistory::STATUS_UNPROCESSED_FIRST_CHARGE, $user, $chargeSource);

        // 初回チャージ未処理状態でないユーザー
        $user = $this->createUser(10009, PolletUser::STATUS_ISSUED);
        $this->createChargeRequest(100009, 'retry', $user, $chargeSource);

        // チャージ申請履歴が存在しないユーザー
        $this->createUser(10010, PolletUser::STATUS_WAITING_ISSUE);

        // セディナIDが重複するユーザー
        $user = $this->createUser(10011, PolletUser::STATUS_WAITING_ISSUE);
        $this->createChargeRequest(100011, ChargeRequestHistory::STATUS_UNPROCESSED_FIRST_CHARGE, $user, $chargeSource);
        $user = $this->createUser(10012, PolletUser::STATUS_WAITING_ISSUE);
        $this->createChargeRequest(100012, ChargeRequestHistory::STATUS_UNPROCESSED_FIRST_CHARGE, $user, $chargeSource);

        // 受け取ったセディナIDが数字以外を含む
        $user = $this->createUser(10013, PolletUser::STATUS_WAITING_ISSUE);
        $this->createChargeRequest(100013, ChargeRequestHistory::STATUS_UNPROCESSED_FIRST_CHARGE, $user, $chargeSource);

        // 受け取ったセディナIDが0より小さい
        $user = $this->createUser(10014, PolletUser::STATUS_WAITING_ISSUE);
        $this->createChargeRequest(100014, ChargeRequestHistory::STATUS_UNPROCESSED_FIRST_CHARGE, $user, $chargeSource);

        // 受け取ったセディナIDが16桁より大きい
        $user = $this->createUser(10015, PolletUser::STATUS_WAITING_ISSUE);
        $this->createChargeRequest(100015, ChargeRequestHistory::STATUS_UNPROCESSED_FIRST_CHARGE, $user, $chargeSource);
    }

    private function createChargeSource()
    {
        $chargeSource = new ChargeSource();
        $chargeSource->charge_source_code = 'testcharge';
        $chargeSource->site_name = 'testcharge';
        $chargeSource->min_value = 300;
        $chargeSource->card_issue_fee = 0;
        $chargeSource->url = 'http://testcharge.com/';
        $chargeSource->introduce_charge_rate_point = 1;
        $chargeSource->introduce_charge_rate_price = 1;
        $chargeSource->description = 'testcharge';
        $chargeSource->publishing_status = 'public';
        $chargeSource->cooperation_type = ChargeSource::COOPERATION_TYPE_POINT_SITE_API;
        $chargeSource->save();

        return $chargeSource;
    }

    private function createUser(int $polletId, string $registrationStatus)
    {
        $faker = Faker\Factory::create();
        $user = new PolletUser();
        $user->id = $polletId;
        $user->user_code_secret = $faker->md5;
        $user->cedyna_id = $faker->regexify('[0-9]{16}');
        $user->mail_address = $faker->email;
        $user->registration_status = $registrationStatus;
        $user->balance_at_charge = 0;
        $user->save();

        return $user;
    }

    private function createChargeRequest(
        int $id,
        string $processingStatus,
        PolletUser $user,
        ChargeSource $chargeSource
    ) {
        $chargeRequestHistory = new ChargeRequestHistory();
        $chargeRequestHistory->id = $id;
        $chargeRequestHistory->pollet_user_id = $user->id;
        $chargeRequestHistory->charge_source_code = $chargeSource->charge_source_code;
        $chargeRequestHistory->exchange_value = 1000;
        $chargeRequestHistory->charge_value = 1000 - $chargeSource->card_issue_fee;
        $chargeRequestHistory->processing_status = $processingStatus;
        $chargeRequestHistory->cause = 'テストチャージ';
        $chargeRequestHistory->save();

        return $chargeRequestHistory;
    }
}
