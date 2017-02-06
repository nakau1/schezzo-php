<?php
namespace tests\unit\fixtures;

use app\models\ChargeRequestHistory;
use app\models\ChargeSource;
use app\models\PolletUser;
use Faker;

class FirstChargeRequestFixture extends PolletDbFixture
{
    public function load()
    {
        // 共通で使うチャージ元
        $chargeSource = $this->createChargeSource();

        // 通常の更新対象ユーザー
        $user = $this->createUser(10001);
        $this->createChargeRequest(100001, 'unprocessed_first_charge', $user, $chargeSource);

        // チャージ申請を持たないユーザー
        $this->createUser(10002);

        // 複数のチャージ申請を持つユーザー
        $user = $this->createUser(10003);
        $this->createChargeRequest(100002, 'unprocessed_first_charge', $user, $chargeSource);
        $this->createChargeRequest(100003, 'unprocessed_first_charge', $user, $chargeSource);

        // チャージ申請に定義済の処理状態を持つユーザー
        $user = $this->createUser(10004);
        $this->createChargeRequest(100004, 'ready', $user, $chargeSource);

        // チャージ申請に未定義の処理状態を持つユーザー
        $user = $this->createUser(10005);
        $this->createChargeRequest(100005, 'test_undefined_status', $user, $chargeSource);

        // 一部だけ初回チャージ未処理のユーザー
        $user = $this->createUser(10006);
        $this->createChargeRequest(100006, 'test_undefined_status', $user, $chargeSource);
        $this->createChargeRequest(100007, 'unprocessed_first_charge', $user, $chargeSource);

        // 更新非対象ユーザー
        $user = $this->createUser(10007);
        $this->createChargeRequest(100008, 'unprocessed_first_charge', $user, $chargeSource);
    }

    /**
     * @return ChargeSource
     */
    protected function createChargeSource()
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

    private function createUser(int $polletId)
    {
        $faker = Faker\Factory::create();
        $user = new PolletUser();
        $user->id = $polletId;
        $user->user_code_secret = $faker->md5;
        $user->cedyna_id = $faker->regexify('[0-9]{16}');
        $user->mail_address = $faker->email;
        $user->registration_status = 'finished_first_charge';
        $user->balance_at_charge = 0;
        $user->save();

        return $user;
    }

    private function createChargeRequest(int $id, string $status, PolletUser $user, ChargeSource $chargeSource)
    {
        $chargeRequestHistory = new ChargeRequestHistory();
        $chargeRequestHistory->id = $id;
        $chargeRequestHistory->pollet_user_id = $user->id;
        $chargeRequestHistory->charge_source_code = $chargeSource->charge_source_code;
        $chargeRequestHistory->exchange_value = 1000;
        $chargeRequestHistory->charge_value = 1000 - $chargeSource->card_issue_fee;
        $chargeRequestHistory->processing_status = $status;
        $chargeRequestHistory->cause = 'テストチャージ';
        $chargeRequestHistory->save();

        return $chargeRequestHistory;
    }
}
