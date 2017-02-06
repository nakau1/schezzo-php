<?php

namespace tests\unit\models;

use app\models\ChargeRequestHistory;
use app\models\FirstChargeRequest;
use app\models\PolletUser;
use Faker;
use tests\unit\fixtures\FirstChargeRequestFixture;
use yii\codeception\TestCase;

class FirstChargeRequestTest extends TestCase
{
    public $appConfig = '@app/config/console.php';

    public function setUp()
    {
        parent::setUp();
    }

    public function fixtures()
    {
        return [
            'fixture' => FirstChargeRequestFixture::class
        ];
    }

    /**
     * @test
     */
    public function チャージ申請を処理待ち状態にできる()
    {
        $updateUser = PolletUser::find()->active()->where(['id' => 10001])->one();
        $model = new FirstChargeRequest();
        $model->ready($updateUser);

        $user = PolletUser::find()->active()->where(['id' => 10001])->one();
        $this->assertEquals('ready', $user->chargeRequestHistories[0]->processing_status);
    }

    /**
     * @test
     */
    public function チャージ申請を処理待ち状態にするときに状態以外の情報を書き換えない()
    {
        $updateUser = PolletUser::find()->active()->where(['id' => 10001])->one();

        $beforeId = $updateUser->chargeRequestHistories[0]->id;
        $beforePolletId = $updateUser->chargeRequestHistories[0]->pollet_user_id;
        $beforeChargeSourceCode = $updateUser->chargeRequestHistories[0]->charge_source_code;
        $beforeChargeValue = $updateUser->chargeRequestHistories[0]->charge_value;
        $beforeExchangeValue = $updateUser->chargeRequestHistories[0]->exchange_value;
        $beforeCause = $updateUser->chargeRequestHistories[0]->cause;
        $beforeRegisteredDate = $updateUser->chargeRequestHistories[0]->created_at;

        $model = new FirstChargeRequest();
        $model->ready($updateUser);

        $user = PolletUser::find()->active()->where(['id' => 10001])->one();
        $this->assertEquals($beforeId, $user->chargeRequestHistories[0]->id);
        $this->assertEquals($beforePolletId, $user->chargeRequestHistories[0]->pollet_user_id);
        $this->assertEquals($beforeChargeSourceCode, $user->chargeRequestHistories[0]->charge_source_code);
        $this->assertEquals($beforeChargeValue, $user->chargeRequestHistories[0]->charge_value);
        $this->assertEquals($beforeExchangeValue, $user->chargeRequestHistories[0]->exchange_value);
        $this->assertEquals($beforeCause, $user->chargeRequestHistories[0]->cause);
        $this->assertEquals($beforeRegisteredDate, $user->chargeRequestHistories[0]->created_at);
    }

    /**
     * @test
     */
    public function チャージ申請を処理待ち状態にするときに他人の処理状態を書き換えない()
    {
        $shouldNotUpdateUser = PolletUser::find()->active()->where(['id' => 10007])->one();
        $beforeProcessingStatus = $shouldNotUpdateUser->chargeRequestHistories[0]->processing_status;

        $updateUser = PolletUser::find()->active()->where(['id' => 10001])->one();
        $model = new FirstChargeRequest();
        $model->ready($updateUser);

        $user = PolletUser::find()->active()->where(['id' => 10007])->one();
        $this->assertEquals($beforeProcessingStatus, $user->chargeRequestHistories[0]->processing_status);
    }

    /**
     * @test
     */
    public function ユーザーがチャージ申請を持たない場合も成功とする()
    {
        // 何かおかしいがカード自体は発行されてしまっているのでログを出して許容する

        $userHasNoChargeRequestHistories = PolletUser::find()->active()->where(['id' => 10002])->one();
        $model = new FirstChargeRequest();

        $this->assertTrue($model->ready($userHasNoChargeRequestHistories));
    }

    /**
     * @test
     */
    public function ユーザーがチャージ申請を持たない場合に他のチャージ申請を更新しない()
    {
        $shouldNotUpdateUser = PolletUser::find()->active()->where(['id' => 10007])->one();
        $beforeProcessingStatus = $shouldNotUpdateUser->chargeRequestHistories[0]->processing_status;

        $userHasNoChargeRequestHistories = PolletUser::find()->active()->where(['id' => 10002])->one();
        $model = new FirstChargeRequest();
        $model->ready($userHasNoChargeRequestHistories);

        $user = PolletUser::find()->active()->where(['id' => 10007])->one();
        $this->assertEquals($beforeProcessingStatus, $user->chargeRequestHistories[0]->processing_status);
    }

    /**
     * @test
     */
    public function ユーザーが複数のチャージ申請を持つ場合にすべて処理待ち状態にする()
    {
        $updateUser = PolletUser::find()->active()->where(['id' => 10003])->one();
        $model = new FirstChargeRequest();
        $model->ready($updateUser);

        $user = PolletUser::find()->active()->where(['id' => 10003])->one();
        $this->assertEquals('ready', $user->chargeRequestHistories[0]->processing_status);
        $this->assertEquals('ready', $user->chargeRequestHistories[1]->processing_status);
    }

    /**
     * @test
     * @dataProvider チャージ申請の処理状態が初回チャージ未処理ではないpolletId
     */
    public function チャージ申請の処理状態が初回チャージ未処理ではない場合に処理状態を更新しない(int $polletId)
    {
        $shouldNotUpdateUser = PolletUser::find()->active()->where(['id' => $polletId])->one();
        $beforeProcessingStatus = $shouldNotUpdateUser->chargeRequestHistories[0]->processing_status;
        $model = new FirstChargeRequest();
        $model->ready($shouldNotUpdateUser);

        $user = PolletUser::find()->active()->where(['id' => $polletId])->one();
        $this->assertEquals($beforeProcessingStatus, $user->chargeRequestHistories[0]->processing_status);
    }

    /**
     * @return array
     */
    public function チャージ申請の処理状態が初回チャージ未処理ではないpolletId()
    {
        return [
            '定義済の処理状態（ready）'                 => [10004],
            '未定義の処理状態（test_undefined_status）' => [10005],
        ];
    }

    /**
     * @test
     */
    public function チャージ申請の処理状態が一部だけ初回チャージ未処理の場合にその処理状態を更新する()
    {
        $shouldUpdateChargeRequest = ChargeRequestHistory::find()->where(['id' => 100007])->one();
        $model = new FirstChargeRequest();
        $model->ready(PolletUser::find()->active()->where(['id' => $shouldUpdateChargeRequest->pollet_user_id])->one());

        $chargeRequest = ChargeRequestHistory::find()->where(['id' => 100007])->one();
        $this->assertEquals('ready', $chargeRequest->processing_status);
    }

    /**
     * @test
     */
    public function チャージ申請の処理状態が一部だけ初回チャージ未処理の場合にそれ以外の処理状態を更新しない()
    {
        $shouldNotUpdateChargeRequest = ChargeRequestHistory::find()->where(['id' => 100006])->one();
        $beforeProcessingStatus = $shouldNotUpdateChargeRequest->processing_status;
        $model = new FirstChargeRequest();
        $model->ready(PolletUser::find()->active()->where(['id' => $shouldNotUpdateChargeRequest->pollet_user_id])->one());

        $chargeRequest = ChargeRequestHistory::find()->where(['id' => 100006])->one();
        $this->assertEquals($beforeProcessingStatus, $chargeRequest->processing_status);
    }
}