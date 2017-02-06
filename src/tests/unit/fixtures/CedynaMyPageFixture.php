<?php
namespace tests\unit\fixtures;

use app\models\PolletUser;
use Faker;
use Yii;

class CedynaMyPageFixture extends PolletDbFixture
{
    public static $polletUserId = 1;
    public static $cedynaId = '0002926000232698';
    public static $password = 'Passw0rd';

    public function load()
    {
        $faker = Faker\Factory::create();
        $user = new PolletUser();
        $user->id = self::$polletUserId;
        $user->user_code_secret = $faker->md5;
        $user->cedyna_id = self::$cedynaId;
        $user->rawPassword = self::$password;
        $user->mail_address = $faker->email;
        $user->registration_status = PolletUser::STATUS_ACTIVATED;
        $user->balance_at_charge = 0;
        $user->save();
    }
}
