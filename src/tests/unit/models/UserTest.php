<?php

namespace tests\unit\models;

use app\models\PolletUser;
use tests\unit\fixtures\PolletUserFixture;
use yii\codeception\TestCase;
use Faker;

class PolletUserTest extends TestCase
{
    public $appConfig = '@app/config/console.php';

    public function setUp()
    {
        parent::setUp();
    }

    public function fixtures()
    {
        return [
            'fixture' => PolletUserFixture::class
        ];
    }

    /**
     * @test
     */
    public function パスワードが暗号化されてDBに保存される()
    {
        $user = PolletUser::find()->where(['id' => PolletUserFixture::$polletUserId])->one();
        $user->cedyna_id = '1234567890123456';
        $user->rawPassword = 'Passw0rd';
        $user->save();

        $user->refresh();
        $this->assertNotEquals('Passw0rd', $user->encrypted_password);
    }

    /**
     * @test
     */
    public function DBに保存されたパスワードを復号できる()
    {
        $user = PolletUser::find()->where(['id' => PolletUserFixture::$polletUserId])->one();
        $user->cedyna_id = '1234567890123456';
        $user->rawPassword = 'Passw0rd';
        $user->save();

        $user->refresh();
        $this->assertEquals('Passw0rd', $user->rawPassword);
    }

    /**
     * @test
     */
    public function セディナIDが違う場合復号できない()
    {
        $user = PolletUser::find()->where(['id' => PolletUserFixture::$polletUserId])->one();
        $user->cedyna_id = '1234567890123456';
        $user->rawPassword = 'Passw0rd';
        $user->save();

        $user->refresh();
        $user->cedyna_id = '6543210987654321';
        $this->assertNotEquals('Passw0rd', $user->rawPassword);
    }
}