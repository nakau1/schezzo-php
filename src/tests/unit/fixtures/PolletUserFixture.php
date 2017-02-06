<?php
namespace tests\unit\fixtures;

use app\models\PolletUser;
use Faker;

class PolletUserFixture extends PolletDbFixture
{
    public static $polletUserId = 1;

    public function load()
    {
        $faker = Faker\Factory::create();
        $user = new PolletUser();
        $user->id = self::$polletUserId;
        $user->user_code_secret = $faker->md5;
        $user->registration_status = PolletUser::STATUS_ACTIVATED;
        $user->save();
    }
}
