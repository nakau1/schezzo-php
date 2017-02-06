<?php
namespace app\commands;

use app\components\GeneralChargeBonus;
use app\components\HulftDummy;
use app\helpers\Date;
use app\models\AdminUser;
use app\models\CardValueCache;
use app\models\cedyna_files\CedynaFile;
use app\models\ChargeErrorHistory;
use app\models\ChargeRequestHistory;
use app\models\ChargeSource;
use app\models\Information;
use app\models\Inquiry;
use app\models\InquiryReply;
use app\models\MonthlyTradingHistoryCache;
use app\models\PointSiteApi;
use app\models\PointSiteToken;
use app\models\PolletUser;
use app\models\PushInformationOpening;
use app\models\PushNotificationToken;
use app\models\Reception;
use Faker\Factory;
use Faker\Generator;
use Yii;
use yii\base\ErrorException;
use yii\console\Controller;
use yii\db\ActiveRecord;
use yii\db\IntegrityException;

/**
 * 負荷試験用データ投入コマンド
 *
 * Class LoadTestDataController
 * @package app\commands
 */
class LoadTestDataController extends Controller
{
    const COUNT_ADMIN_USER = 10;
    const COUNT_CHARGE_SOURCE = 20;
    const COUNT_POLLET_USER_ACTIVATED = 200000;
    const COUNT_POLLET_USER_NEW_USER = 100000;
    const COUNT_POLLET_USER_SITE_AUTHENTICATED = 10000;
    const COUNT_POLLET_USER_CHARGE_REQUESTED = 100000;
    const COUNT_POLLET_USER_WAITING_ISSUE = 10000;
    const COUNT_POLLET_USER_ISSUED = 10000;
    const COUNT_POLLET_USER_SIGN_OUT = 10000;
    const COUNT_POLLET_USER_REMOVED = 10000;
    // アクティベート前は絶対1
    const COUNT_POINT_SITE_TOKEN_PER_ACTIVATED_USER = 5;
    // アクティベート前は絶対1
    const COUNT_CHARGE_REQUEST_HISTORY_PER_ACTIVATED_USER = 3;
    const COUNT_INFORMATION = 50;
    // アクティベートユーザのこれだけが1回チャージエラーになる
    const CHARGE_ERROR_PERCENTAGE = 1 / 60;
    // アクティベートユーザのこれだけがすべてのお知らせを読む
    const OPEN_PUSH_INFORMATION_PERCENTAGE = 12 / 100;
    // アクティベートユーザのこれだけが1回お問い合わせする
    const INQUIRY_PERCENTAGE = 1 / 20;
    // アクティベートユーザのこれだけが12ヶ月分の利用履歴キャッシュがある（他の人は0）
    const HAS_TRADING_HISTORY_CACHES_PERCENTAGE = 1 / 4;

    // バッチ用登録直後のユーザ数
    const COUNT_HULFT_POLLET_USER_FIRST_CHARGE = 10000;
    // バッチ用チャージが2回目以降のユーザ数
    const COUNT_HULFT_POLLET_USER_ACTIVATED = 5000;
    const COUNT_HULFT_TRADING_HISTORY = 900000;

    /** @var Generator */
    private $faker;

    /** @var string[] */
    private $actualCedynaIds;

    public function init()
    {
        parent::init();

        $this->faker = Factory::create('ja_JP');
        // 作ってもらったテストアカウント50件 17/02/03 時点
        // https://docs.google.com/spreadsheets/d/1DLW6QanwZ2nDhCI5c6LmfnwX_ewYkYRex5lWH-MvjIk/edit#gid=1524197953
        $this->actualCedynaIds = [
            '0002500189314807',
            '0002729583194859',
            '0002077603880366',
            '0002380654149602',
            '0002683704418848',
            '0002986754688081',
            '0002870445006335',
            '0002218465691844',
            '0002521515961087',
            '0002824566230323',
            '0002172586915832',
            '0002475637185077',
            '0002778687454313',
            '0002126708139820',
            '0002010398458072',
            '0002313448727318',
            '0002616498996553',
            '0002919549265790',
            '0002193913562113',
            '0002496963831357',
            '0002800014100599',
            '0002148034786108',
            '0002031725104353',
            '0002334775373591',
            '0002637825642835',
            '0002940875912078',
            '0002288896597589',
            '0002591946866825',
            '0002894997136068',
            '0002243017821574',
            '0002546068090810',
            '0002429758409063',
            '0002732808678300',
            '0002080829363811',
            '0002383879633053',
            '0002613273513109',
            '0002916323782347',
            '0002264344467857',
            '0002567394737098',
            '0002451085055344',
            '0002754135324580',
            '0002102156010094',
            '0002405206279332',
            '0002708256548575',
            '0002056277234084',
            '0002359327503320',
            '0002662377772566',
            '0002965428041802',
            '0002849118360054',
            '0002197139045563',
        ];
    }

    /**
     * コマンドを実行できる環境を制限する
     */
    private function validateEnvironment()
    {
        $permitted = in_array(YII_ENV, ['dev', 'demo', 'test'], true);
        if (!$permitted) {
            throw new ErrorException('この環境での実行は禁止されています');
        }
    }

    private function deleteAllData()
    {
        // 子テーブルにデータがあるテーブルを消せないので、何回かやり直す
        for ($i = 0; $i < 3; $i++) {
            // DBのデータをすべて削除する
            foreach (ActiveRecord::getDb()->getSchema()->tableSchemas as $table) {
                if ($table->fullName === 'migration') {
                    // 消してしまうとマイグレーションが失敗する
                    continue;
                }

                try {
                    ActiveRecord::getDb()->createCommand()->delete($table->fullName)->execute();
                } catch (IntegrityException $e) {
                    // 小テーブルにデータを持つ親テーブルのレコードを消そうとした。
                    // 小テーブルのデータを消してからやり直す
                    continue;
                }
                if ($table->sequenceName !== null) {
                    ActiveRecord::getDb()->createCommand()->resetSequence($table->fullName, 1)->execute();
                }
            }
        }
    }

    /**
     * @throws ErrorException
     */
    public function actionIndex()
    {
        $this->validateEnvironment();
        $this->deleteAllData();

        $this->insertDefaultData();
    }

    /**
     * @throws ErrorException
     */
    public function actionForHulft()
    {
        $this->validateEnvironment();
        $this->deleteAllData();

        $this->insertDefaultData();

        echo 'make data for hulft'.PHP_EOL;
        $this->makeDataForHulft();
    }

    private function insertDefaultData()
    {
        echo 'insert admin users'.PHP_EOL;
        $this->insertAdminUsers();
        echo 'insert information'.PHP_EOL;
        $this->insertInformation();
        echo 'insert charge sources'.PHP_EOL;
        $this->insertChargeSources();
        echo 'insert pollet users'.PHP_EOL;
        $this->insertPolletUsers();
    }

    private function insertAdminUsers()
    {
        for ($i = 0; $i < self::COUNT_ADMIN_USER; $i++) {
            $user = new AdminUser();
            $user->name = $this->faker->name;
            if (!$user->save()) {
                var_dump($user->getFirstErrors());
            }
        }
    }

    private function insertChargeSources()
    {
        for ($i = 0; $i < self::COUNT_CHARGE_SOURCE; $i++) {
            $chargeSource = new ChargeSource();
            $chargeSource->charge_source_code = Yii::$app->security->generateRandomString(5);
            $chargeSource->api_key = Yii::$app->security->generateRandomString();
            $chargeSource->site_name = $this->faker->company.$this->faker->firstName;
            $chargeSource->min_value = 1;
            $chargeSource->card_issue_fee = 0;
            $chargeSource->url = 'https://www.polletcorp.com';
            $chargeSource->icon_image_url = $this->faker->imageUrl(240, 240);
            $chargeSource->denomination = 'pt';
            $chargeSource->introduce_charge_rate_point = 1;
            $chargeSource->introduce_charge_rate_price = 1;
            $chargeSource->description = $this->faker->text;
            $chargeSource->auth_url = 'https://testapp.pollet.me/demo/authenticate';
            $chargeSource->publishing_status = ChargeSource::PUBLISHING_STATUS_PUBLIC;
            $chargeSource->cooperation_type = ChargeSource::COOPERATION_TYPE_POINT_SITE_API;
            if (!$chargeSource->save()) {
                var_dump($chargeSource->getFirstErrors());
            }

            $this->insertPointSiteApis($chargeSource->charge_source_code);
        }
    }

    private function insertPointSiteApis(string $chargeSourceCode)
    {
        $api = new PointSiteApi();
        $api->charge_source_code = $chargeSourceCode;
        $api->api_name = PointSiteApi::API_NAME_FETCH_POINT;
        $api->url = 'http://localhost/demo/api-point';
        $api->publishing_status = PointSiteApi::PUBLISHING_STATUS_PUBLIC;
        if (!$api->save()) {
            var_dump($api->getFirstErrors());
        }

        $api = new PointSiteApi();
        $api->charge_source_code = $chargeSourceCode;
        $api->api_name = PointSiteApi::API_NAME_EXCHANGE;
        $api->url = 'http://localhost/demo/api-exchange';
        $api->publishing_status = PointSiteApi::PUBLISHING_STATUS_PUBLIC;
        if (!$api->save()) {
            var_dump($api->getFirstErrors());
        }

        $api = new PointSiteApi();
        $api->charge_source_code = $chargeSourceCode;
        $api->api_name = PointSiteApi::API_NAME_CANCEL_EXCHANGE;
        $api->url = 'http://localhost/demo/api-cancel-exchange';
        $api->publishing_status = PointSiteApi::PUBLISHING_STATUS_PUBLIC;
        if (!$api->save()) {
            var_dump($api->getFirstErrors());
        }

        $api = new PointSiteApi();
        $api->charge_source_code = $chargeSourceCode;
        $api->api_name = PointSiteApi::API_NAME_REQUEST_TOKEN;
        $api->url = 'http://localhost/demo/api-token';
        $api->publishing_status = PointSiteApi::PUBLISHING_STATUS_PUBLIC;
        if (!$api->save()) {
            var_dump($api->getFirstErrors());
        }
    }

    private function insertInformation()
    {
        for ($i = 0; $i < self::COUNT_INFORMATION; $i++) {
            $information = new Information();
            $information->heading = $this->faker->text(50);
            $information->body = $this->faker->text(500);
            $information->begin_date = Date::create()->subMinutes(10)->subWeeks($i)->format('Y-m-d H:i:s');
            $information->end_date = Date::create()->addDays(5)->format('Y-m-d H:i:s');
            $information->sends_push = 1;
            $information->is_important = 1;
            $information->publishing_status = Information::PUBLISHING_STATUS_PUBLIC;
            if (!$information->save()) {
                var_dump($information->getFirstErrors());
            }
        }
    }

    private function insertPolletUsers()
    {
        for ($i = 0; $i < self::COUNT_POLLET_USER_NEW_USER; $i++) {
            $polletUser = $this->insertPolletUsersNewUser();

            $this->insertPushNotificationToken($polletUser->id);
        }
        for ($i = 0; $i < self::COUNT_POLLET_USER_SITE_AUTHENTICATED; $i++) {
            $polletUser = $this->insertPolletUsersSiteAuthenticated();

            $this->insertPushNotificationToken($polletUser->id);
            $this->insertPointSiteTokens($polletUser->id, 1);
        }
        for ($i = 0; $i < self::COUNT_POLLET_USER_CHARGE_REQUESTED; $i++) {
            $polletUser = $this->insertPolletUsersChargeRequested();

            $this->insertPushNotificationToken($polletUser->id);
            $this->insertPointSiteTokens($polletUser->id, 1);
            $this->insertChargeRequestHistoriesFirst($polletUser->id, 1);
        }
        for ($i = 0; $i < self::COUNT_POLLET_USER_WAITING_ISSUE; $i++) {
            $polletUser = $this->insertPolletUsersWaitingIssue();

            $this->insertPushNotificationToken($polletUser->id);
            $this->insertPointSiteTokens($polletUser->id, 1);
            $this->insertChargeRequestHistoriesFirst($polletUser->id, 1);
        }
        for ($i = 0; $i < self::COUNT_POLLET_USER_ISSUED; $i++) {
            $polletUser = $this->insertPolletUsersIssued();

            $this->insertPushNotificationToken($polletUser->id);
            $this->insertPointSiteTokens($polletUser->id, 1);
            $this->insertChargeRequestHistoriesApplied($polletUser->id, 1);
        }
        for ($i = 0; $i < self::COUNT_POLLET_USER_ACTIVATED; $i++) {
            $polletUser = $this->insertPolletUsersActivated();

            $this->insertPushNotificationToken($polletUser->id);
            $this->insertPointSiteTokens($polletUser->id, self::COUNT_POINT_SITE_TOKEN_PER_ACTIVATED_USER);
            $this->insertChargeRequestHistoriesApplied(
                $polletUser->id,
                self::COUNT_CHARGE_REQUEST_HISTORY_PER_ACTIVATED_USER
            );
            if ($i % floor(1 / self::CHARGE_ERROR_PERCENTAGE) == 0) {
                $this->insertChargeRequestHistoriesError($polletUser->id, 1);
            }
            if ($i % floor(1 / self::OPEN_PUSH_INFORMATION_PERCENTAGE) == 0) {
                $this->insertPushInformationOpenings($polletUser->id);
            }
            if ($i % floor(1 / self::INQUIRY_PERCENTAGE) == 0) {
                $this->insertInquiries($polletUser->id);
            }
            $this->insertCardValueCache($polletUser->id);
            if ($i % floor(1 / self::HAS_TRADING_HISTORY_CACHES_PERCENTAGE) == 0) {
                $this->insertMonthlyTradingHistoryCaches($polletUser->id);
            }
        }
        foreach ($this->actualCedynaIds as $i => $cedynaId) {
            $polletUser = $this->insertPolletUsersActivated($cedynaId);

            $this->insertPushNotificationToken($polletUser->id);
            $this->insertPointSiteTokens($polletUser->id, self::COUNT_POINT_SITE_TOKEN_PER_ACTIVATED_USER);
            $this->insertChargeRequestHistoriesApplied(
                $polletUser->id,
                self::COUNT_CHARGE_REQUEST_HISTORY_PER_ACTIVATED_USER
            );
            if ($i % floor(1 / self::CHARGE_ERROR_PERCENTAGE) == 0) {
                $this->insertChargeRequestHistoriesError($polletUser->id, 1);
            }
            if ($i % floor(1 / self::OPEN_PUSH_INFORMATION_PERCENTAGE) == 0) {
                $this->insertPushInformationOpenings($polletUser->id);
            }
            if ($i % floor(1 / self::INQUIRY_PERCENTAGE) == 0) {
                $this->insertInquiries($polletUser->id);
            }
            $this->insertCardValueCache($polletUser->id);
            if ($i % floor(1 / self::HAS_TRADING_HISTORY_CACHES_PERCENTAGE) == 0) {
                $this->insertMonthlyTradingHistoryCaches($polletUser->id);
            }
        }
        for ($i = 0; $i < self::COUNT_POLLET_USER_SIGN_OUT; $i++) {
            $polletUser = $this->insertPolletUsersSignOut();

            $this->insertPushNotificationToken($polletUser->id);
        }
        for ($i = 0; $i < self::COUNT_POLLET_USER_REMOVED; $i++) {
            $polletUser = $this->insertPolletUsersRemoved();

            $this->insertPushNotificationToken($polletUser->id);
        }
    }

    private function insertPointSiteTokens(int $polletUserId, int $count)
    {
        $chargeSourceCodes = $this->faker->shuffleArray($this->findAllChargeSourceCodes());
        for ($i = 0; $i < $count; $i++) {
            $token = new PointSiteToken();
            $token->pollet_user_id = $polletUserId;
            $token->charge_source_code = $chargeSourceCodes[$i];
            $token->token = Yii::$app->security->generateRandomString();
            if (!$token->save()) {
                var_dump($token->getFirstErrors());
            }
        }
    }

    private function insertPushNotificationToken(int $polletUserId)
    {
        $token = new PushNotificationToken();
        $token->pollet_user_id = $polletUserId;
        $token->device_id = $this->faker->uuid;
        $token->token = Yii::$app->security->generateRandomString();
        $token->platform = $this->faker->randomElement(['ios', 'android']);
        $token->is_active = 0;
        if (!$token->save()) {
            var_dump($token->getFirstErrors());
        }
    }

    private function insertChargeRequestHistoriesApplied(int $polletUserId, int $count)
    {
        $chargeSourceCodes = $this->findAllChargeSourceCodes();
        for ($i = 0; $i < $count; $i++) {
            $request = $this->makeCommonChargeRequestHistory($polletUserId);
            $request->charge_source_code = $this->faker->randomElement($chargeSourceCodes);
            $request->processing_status = ChargeRequestHistory::STATUS_APPLIED_CHARGE;
            if (!$request->save()) {
                var_dump($request->getFirstErrors());
            }

            $this->insertReceptions($request);
        }
    }

    private function insertChargeRequestHistoriesReady(int $polletUserId, int $count)
    {
        $chargeSourceCodes = $this->findAllChargeSourceCodes();
        for ($i = 0; $i < $count; $i++) {
            $request = $this->makeCommonChargeRequestHistory($polletUserId);
            $request->charge_source_code = $this->faker->randomElement($chargeSourceCodes);
            $request->processing_status = ChargeRequestHistory::STATUS_READY;
            if (!$request->save()) {
                var_dump($request->getFirstErrors());
            }

            $this->insertReceptions($request);
        }
    }

    private function insertChargeRequestHistoriesRequested(int $polletUserId, int $count)
    {
        $ret = [];
        $chargeSourceCodes = $this->findAllChargeSourceCodes();
        for ($i = 0; $i < $count; $i++) {
            $request = $this->makeCommonChargeRequestHistory($polletUserId);
            $request->charge_source_code = $this->faker->randomElement($chargeSourceCodes);
            $request->processing_status = ChargeRequestHistory::STATUS_REQUESTED_CHARGE;
            if (!$request->save()) {
                var_dump($request->getFirstErrors());
            }

            $this->insertReceptions($request);
            $ret[] = $request;
        }

        return $ret;
    }

    private function insertChargeRequestHistoriesFirst(int $polletUserId, int $count)
    {
        $chargeSourceCodes = $this->findAllChargeSourceCodes();
        for ($i = 0; $i < $count; $i++) {
            $request = $this->makeCommonChargeRequestHistory($polletUserId);
            $request->charge_source_code = $this->faker->randomElement($chargeSourceCodes);
            $request->processing_status = ChargeRequestHistory::STATUS_UNPROCESSED_FIRST_CHARGE;
            if (!$request->save()) {
                var_dump($request->getFirstErrors());
            }

            $this->insertReceptions($request);
        }
    }

    private function insertChargeRequestHistoriesError(int $polletUserId, int $count)
    {
        $chargeSourceCodes = $this->findAllChargeSourceCodes();
        for ($i = 0; $i < $count; $i++) {
            $request = $this->makeCommonChargeRequestHistory($polletUserId);
            $request->charge_source_code = $this->faker->randomElement($chargeSourceCodes);
            $request->processing_status = ChargeRequestHistory::STATUS_ERROR;
            if (!$request->save()) {
                var_dump($request->getFirstErrors());
            }

            $this->insertChargeErrorHistory($request->id);
            $this->insertReceptions($request);
        }
    }

    private function makeCommonChargeRequestHistory(int $polletUserId)
    {
        $generalBonus = new GeneralChargeBonus();

        $request = new ChargeRequestHistory();
        $request->pollet_user_id = $polletUserId;
        $request->exchange_value = $this->faker->numberBetween(1, 30) * 100;
        $request->charge_value = $generalBonus->applyTo($request->exchange_value);
        $request->cause = $this->faker->text(10);

        return $request;
    }

    private function insertChargeErrorHistory(int $chargeRequestHistoryId)
    {
        $error = new ChargeErrorHistory();
        $error->charge_request_history_id = $chargeRequestHistoryId;
        $error->error_code = '4150';
        $error->raw_data = '"D","0421","CEDYNA","0002","00020001","0002365778470224","0002365778470224","","1","load test","1","4150","1"';
        if (!$error->save()) {
            var_dump($error->getFirstErrors());
        }
    }

    private function insertPushInformationOpenings(int $polletUserId)
    {
        for ($i = 1; $i <= self::COUNT_INFORMATION; $i++) {
            $opening = new PushInformationOpening();
            $opening->pollet_user_id = $polletUserId;
            $opening->information_id = $i;
            if (!$opening->save()) {
                var_dump($opening->getFirstErrors());
            }
        }
    }

    private function insertInquiries(int $polletUserId)
    {
        $inquiry = new Inquiry();
        $inquiry->pollet_user_id = $polletUserId;
        $inquiry->mail_address = $this->faker->email;
        $inquiry->content = $this->faker->text(1000);
        if (!$inquiry->save()) {
            var_dump($inquiry->getFirstErrors());
        }

        $this->insertInquiryReply($inquiry->id);
    }

    private function insertInquiryReply(int $inquiryId)
    {
        $reply = new InquiryReply();
        $reply->inquiry_id = $inquiryId;
        $reply->admin_user_id = $this->faker->numberBetween(1, self::COUNT_ADMIN_USER);
        $reply->content = $this->faker->text(1000);
        if (!$reply->save()) {
            var_dump($reply->getFirstErrors());
        }
    }

    private function insertCardValueCache(int $polletUserId)
    {
        $cache = new CardValueCache();
        $cache->pollet_user_id = $polletUserId;
        $cache->value = $this->faker->numberBetween(0, 500000);
        if (!$cache->save()) {
            var_dump($cache->getFirstErrors());
        }
    }

    private function insertMonthlyTradingHistoryCaches(int $polletUserId)
    {
        for ($i = 0; $i < 12; $i++) {
            $cache = new MonthlyTradingHistoryCache();
            $cache->pollet_user_id = $polletUserId;
            $cache->records_json = json_encode([
                [
                    'shop'         => $this->faker->company,
                    'spent_value'  => $this->faker->numberBetween(0, 10000),
                    'trading_date' => $this->faker->date('Y-m-d H:i:s'),
                    'trading_type' => '決済',
                ],
                [
                    'shop'         => $this->faker->company,
                    'spent_value'  => $this->faker->numberBetween(0, 10000),
                    'trading_date' => $this->faker->date('Y-m-d H:i:s'),
                    'trading_type' => '決済',
                ],
                [
                    'shop'         => $this->faker->company,
                    'spent_value'  => $this->faker->numberBetween(0, 10000),
                    'trading_date' => $this->faker->date('Y-m-d H:i:s'),
                    'trading_type' => '決済',
                ],
            ]);
            $cache->month = Date::create()->subMonths($i)->format('ym');
            if (!$cache->save()) {
                var_dump($cache->getFirstErrors());
            }
        }
    }

    private function insertReceptions(ChargeRequestHistory $chargeRequestHistory)
    {
        $reception = new Reception();
        $reception->reception_code = Yii::$app->security->generateRandomString();
        $reception->pollet_user_id = $chargeRequestHistory->pollet_user_id;
        $reception->charge_source_code = $chargeRequestHistory->charge_source_code;
        $reception->charge_request_history_id = $chargeRequestHistory->id;
        $reception->charge_value = $chargeRequestHistory->charge_value;
        $reception->reception_status = $this->faker->randomElement([
            Reception::RECEPTION_STATUS_ACCEPTED,
            Reception::RECEPTION_STATUS_APPLIED,
            Reception::RECEPTION_STATUS_COMPLETED,
        ]);
        $reception->expiry_date = Date::create()->addMinute(5);
        $reception->by_card_number = $this->faker->numberBetween(0, 1);
        if (!$reception->save()) {
            var_dump($reception->getFirstErrors());
        }
    }

    /**
     * @return string[]
     */
    private function findAllChargeSourceCodes()
    {
        return array_map(function (ChargeSource $chargeSource) {
            return $chargeSource->charge_source_code;
        }, ChargeSource::find()->all());
    }

    /**
     * DB へのインサートとファイルの生成
     */
    private function makeDataForHulft()
    {
        // 入金ファイル送信用
        // issued(初回) + activated
        for ($i = 0; $i < self::COUNT_HULFT_POLLET_USER_FIRST_CHARGE; $i++) {
            $polletUser = $this->insertPolletUsersIssued();

            $this->insertPushNotificationToken($polletUser->id);
            $this->insertPointSiteTokens($polletUser->id, 1);
            $this->insertChargeRequestHistoriesReady($polletUser->id, 1);
        }
        for ($i = 0; $i < self::COUNT_HULFT_POLLET_USER_ACTIVATED; $i++) {
            $polletUser = $this->insertPolletUsersActivated();

            $this->insertPushNotificationToken($polletUser->id);
            $this->insertPointSiteTokens($polletUser->id, self::COUNT_POINT_SITE_TOKEN_PER_ACTIVATED_USER);
            $this->insertChargeRequestHistoriesReady($polletUser->id, 1);
        }

        // 入金ファイル受信用
        // issued(初回) + activated
        $cedynaIdsInPaymentFile = [];
        $chargeIdsInPaymentFile = [];
        for ($i = 0; $i < self::COUNT_HULFT_POLLET_USER_FIRST_CHARGE; $i++) {
            $polletUser = $this->insertPolletUsersIssued();

            $this->insertPushNotificationToken($polletUser->id);
            $this->insertPointSiteTokens($polletUser->id, 1);
            $request = $this->insertChargeRequestHistoriesRequested($polletUser->id, 1);

            $cedynaIdsInPaymentFile[] = $polletUser->cedyna_id;
            $chargeIdsInPaymentFile[] = $request[0]->id;
        }
        for ($i = 0; $i < self::COUNT_HULFT_POLLET_USER_ACTIVATED; $i++) {
            $polletUser = $this->insertPolletUsersActivated();

            $this->insertPushNotificationToken($polletUser->id);
            $this->insertPointSiteTokens($polletUser->id, self::COUNT_POINT_SITE_TOKEN_PER_ACTIVATED_USER);
            $request = $this->insertChargeRequestHistoriesRequested($polletUser->id, 1);

            $cedynaIdsInPaymentFile[] = $polletUser->cedyna_id;
            $chargeIdsInPaymentFile[] = $request[0]->id;
        }
        $this->makeReceivedCedynaPaymentFile($cedynaIdsInPaymentFile, $chargeIdsInPaymentFile);

        // 発番通知受信用
        $polletUserIdsInCedynaIdFile = [];
        for ($i = 0; $i < self::COUNT_HULFT_POLLET_USER_FIRST_CHARGE; $i++) {
            $polletUser = $this->insertPolletUsersWaitingIssue();

            $this->insertPushNotificationToken($polletUser->id);
            $this->insertPointSiteTokens($polletUser->id, 1);
            $this->insertChargeRequestHistoriesFirst($polletUser->id, 1);

            $polletUserIdsInCedynaIdFile[] = $polletUser->id;
        }
        $this->makeReceivedNumberedCedynaIdFile($polletUserIdsInCedynaIdFile);

        // 取引履歴受信用
        $this->makeReceivedTradingHistoryFile();
    }

    /**
     * @return PolletUser
     */
    private function insertPolletUsersNewUser()
    {
        $polletUser = $this->makeCommonPolletUser();
        $polletUser->registration_status = PolletUser::STATUS_NEW_USER;
        if (!$polletUser->save()) {
            var_dump($polletUser->getFirstErrors());
        }

        return $polletUser;
    }

    /**
     * @return PolletUser
     */
    private function insertPolletUsersSiteAuthenticated()
    {
        $polletUser = $this->makeCommonPolletUser();
        $polletUser->registration_status = PolletUser::STATUS_SITE_AUTHENTICATED;
        if (!$polletUser->save()) {
            var_dump($polletUser->getFirstErrors());
        }

        return $polletUser;
    }

    /**
     * @return PolletUser
     */
    private function insertPolletUsersChargeRequested()
    {
        $polletUser = $this->makeCommonPolletUser();
        $polletUser->registration_status = PolletUser::STATUS_CHARGE_REQUESTED;
        if (!$polletUser->save()) {
            var_dump($polletUser->getFirstErrors());
        }

        return $polletUser;
    }

    /**
     * @return PolletUser
     */
    private function insertPolletUsersWaitingIssue()
    {
        $polletUser = $this->makeCommonPolletUser();
        $polletUser->registration_status = PolletUser::STATUS_WAITING_ISSUE;
        $polletUser->mail_address = $this->faker->email;
        if (!$polletUser->save()) {
            var_dump($polletUser->getFirstErrors());
        }

        return $polletUser;
    }

    /**
     * @return PolletUser
     */
    private function insertPolletUsersIssued()
    {
        $polletUser = $this->makeCommonPolletUser();
        $polletUser->registration_status = PolletUser::STATUS_ISSUED;
        $polletUser->mail_address = $this->faker->email;
        $polletUser->cedyna_id = '0002'.$this->faker->regexify('[0-9]{12}');
        $polletUser->rawPassword = $this->faker->password;
        $polletUser->balance_at_charge = $this->faker->numberBetween(1, 5000) * 100;
        if (!$polletUser->save()) {
            var_dump($polletUser->getFirstErrors());
        }

        return $polletUser;
    }

    /**
     * @param null|string $cedynaId
     * @return PolletUser
     */
    private function insertPolletUsersActivated($cedynaId = null)
    {
        $polletUser = $this->makeCommonPolletUser();
        $polletUser->registration_status = PolletUser::STATUS_ACTIVATED;
        $polletUser->mail_address = $this->faker->email;
        $polletUser->cedyna_id = $cedynaId ?? '0002'.$this->faker->regexify('[0-9]{12}');
        $polletUser->rawPassword = $this->faker->password;
        $polletUser->balance_at_charge = $this->faker->numberBetween(1, 5000) * 100;
        if (!$polletUser->save()) {
            var_dump($polletUser->getFirstErrors());
        }

        return $polletUser;
    }

    /**
     * @return PolletUser
     */
    private function insertPolletUsersSignOut()
    {
        $polletUser = $this->makeCommonPolletUser();
        $polletUser->registration_status = PolletUser::STATUS_SIGN_OUT;
        $polletUser->mail_address = $this->faker->email;
        $polletUser->cedyna_id = '0002'.$this->faker->regexify('[0-9]{12}');
        $polletUser->rawPassword = $this->faker->password;
        $polletUser->balance_at_charge = $this->faker->numberBetween(1, 5000) * 100;
        if (!$polletUser->save()) {
            var_dump($polletUser->getFirstErrors());
        }

        return $polletUser;
    }

    /**
     * @return PolletUser
     */
    private function insertPolletUsersRemoved()
    {
        $polletUser = $this->makeCommonPolletUser();
        $polletUser->registration_status = PolletUser::STATUS_REMOVED;
        $polletUser->mail_address = $this->faker->email;
        $polletUser->cedyna_id = '0002'.$this->faker->regexify('[0-9]{12}');
        $polletUser->rawPassword = $this->faker->password;
        $polletUser->balance_at_charge = $this->faker->numberBetween(1, 5000) * 100;
        if (!$polletUser->save()) {
            var_dump($polletUser->getFirstErrors());
        }

        return $polletUser;
    }

    private function makeCommonPolletUser()
    {
        $polletUser = new PolletUser();
        $polletUser->user_code_secret = Yii::$app->security->generateRandomString().'_'.time();

        return $polletUser;
    }

    /**
     * @param string[] $cedynaIdsInPaymentFile
     * @param int[] $chargeIdsInPaymentFile
     */
    private function makeReceivedCedynaPaymentFile(array $cedynaIdsInPaymentFile, array $chargeIdsInPaymentFile)
    {
        $file = new CedynaFile(Yii::$app->params['hulftPath'].'/recv/'.HulftDummy::FILE_RECEIVE_PAYMENT);
        if (file_exists($file->getPath())) {
            $file->remove();
        }
        $csv = '"S"."2017/01/01 00:00:00"'."\n";
        $csv .= '"H","入金種別","イシュアコード","提携先コード","カード種別区分","会員グループ番号","会員番号","カードID","入金額","加盟店名（チャージ理由）","処理結果","エラーコード","処理番号",""'."\n";
        $file->setSaveContent(mb_convert_encoding($csv, 'SJIS'))->save(true);
        foreach (array_combine($cedynaIdsInPaymentFile, $chargeIdsInPaymentFile) as $cedynaId => $chargeId) {
            $csv = <<<CSV
"D","0421","CEDYNA","0002","00020001","{$cedynaId}","{$cedynaId}","","2500","xxからチャージ","0","","{$chargeId}"\n
CSV;
            $file->setSaveContent(mb_convert_encoding($csv, 'SJIS'))->save(true);
        }
        $csv .= '"E","'.sprintf('%8s', count($chargeIdsInPaymentFile)).'"'."\n";
        $file->setSaveContent(mb_convert_encoding($csv, 'SJIS'))->save(true);
    }

    /**
     * @param $polletUserIdsInCedynaIdFile
     */
    private function makeReceivedNumberedCedynaIdFile($polletUserIdsInCedynaIdFile)
    {
        $file = new CedynaFile(Yii::$app->params['hulftPath'].'/recv/'.HulftDummy::FILE_RECEIVE_CEDYNA_ID);
        if (file_exists($file->getPath())) {
            $file->remove();
        }
        $csv = '"S","2017/01/01 00:00:00"'."\n";
        $file->setSaveContent(mb_convert_encoding($csv, 'SJIS'))->save(true);
        foreach ($polletUserIdsInCedynaIdFile as $polletUserId) {
            // 0002から始まる既存のIDと被らないように1002から始める
            $formattedCedynaId = sprintf('%016d', $this->faker->regexify('1002[0-9]{12}'));
            $formattedPolletId = sprintf('%016d', $polletUserId);
            $csv = <<<CSV
'"D","000000000000000000","{$formattedCedynaId}","{$formattedPolletId}","0001","20170101","20170101","20"\n
CSV;
            $file->setSaveContent(mb_convert_encoding($csv, 'SJIS'))->save(true);
        }
        $csv .= '"E",'.sprintf('%8s', count($polletUserIdsInCedynaIdFile))."\n";
        $file->setSaveContent(mb_convert_encoding($csv, 'SJIS'))->save(true);
    }

    private function makeReceivedTradingHistoryFile()
    {
        $file = new CedynaFile(Yii::$app->params['hulftPath'].'/recv/'.HulftDummy::FILE_RECEIVE_TRADING);
        if (file_exists($file->getPath())) {
            $file->remove();
        }
        $csv = '"履歴処理番号","処理日","処理種別","イシュアコード","提携先コード","カード種別区分","会員グループ番号","会員番号","カードID","取引日(現地)","加盟店コード","加盟店名称","処理額"'."\n";
        $file->setSaveContent(mb_convert_encoding($csv, 'SJIS'))->save(true);
        for ($i = 0; $i < self::COUNT_HULFT_TRADING_HISTORY; $i++) {
            $csv = <<<CSV
"000000000001","2016/01/01 13:00:00","0809","CEDYNA","0002","00020001","0002000000000000","0002000000000000","0000000000000000","2016/01/01 13:00:00","shop_code","あじゃじゃ","1000.00"
CSV;
            $file->setSaveContent(mb_convert_encoding($csv, 'SJIS'))->save(true);
        }
    }
}