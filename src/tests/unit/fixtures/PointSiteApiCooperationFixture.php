<?php
namespace tests\unit\fixtures;

use app\models\ChargeSource;
use app\models\PointSiteApi;
use app\models\PointSiteToken;
use app\models\PolletUser;
use Faker;
use Yii;

class PointSiteApiCooperationFixture extends PolletDbFixture
{
    public $polletUserId = 1;
    public $pointSiteCooperatedUser = 2;
    public static $tokenRequestCode = 'tokenRequestCode';
    public static $chargeSource = 'test1';
    public static $chargeSourceName = '公開サイト';
    public static $exchangeApiUrl = 'http://localhost/exchange/';
    public static $cancelExchangeApiUrl = 'http://localhost/cancel-exchange/';
    public static $pointApiUrl = 'http://localhost/point/';
    public static $privatePointSite = 'test2';
    public static $privatePointSiteName = '非公開サイト';

    public function load()
    {
        $faker = Faker\Factory::create();
        $this->publicPointSite($faker);
        $this->privatePointSite($faker);

        //初回チャージ前のユーザー
        $user = new PolletUser();
        $user->id = $this->polletUserId;
        $user->user_code_secret = $faker->md5;
        $user->mail_address = $faker->email;
        $user->registration_status = PolletUser::STATUS_NEW_USER;
        $user->balance_at_charge = 0;
        $user->save();

        //ポイントサイト連携済みのユーザー
        $faker = Faker\Factory::create();
        $user = new PolletUser();
        $user->id = $this->pointSiteCooperatedUser;
        $user->user_code_secret = $faker->md5;
        $user->mail_address = $faker->email;
        $user->registration_status = PolletUser::STATUS_NEW_USER;
        $user->balance_at_charge = 0;
        $user->save();

        //ポイントサイト連携
        $pointSiteToken = new PointSiteToken();
        $pointSiteToken->pollet_user_id = $this->pointSiteCooperatedUser;
        $pointSiteToken->charge_source_code = self::$chargeSource;
        $pointSiteToken->token = $faker->regexify('[A-Z0-9._%+-]{10}');
        $pointSiteToken->save();
    }

    private function publicPointSite(Faker\Generator $faker)
    {
        //ポイントサイトの登録
        $chargeSource = new ChargeSource();
        $chargeSource->charge_source_code = self::$chargeSource;
        $chargeSource->site_name = self::$chargeSourceName;
        $chargeSource->min_value = 300;
        $chargeSource->card_issue_fee = 100;
        $chargeSource->url = $faker->url;
        $chargeSource->icon_image_url = $faker->imageUrl();
        $chargeSource->denomination = 'pt';
        $chargeSource->introduce_charge_rate_point = 1;
        $chargeSource->introduce_charge_rate_price = 1;
        $chargeSource->description = $faker->text(100);
        $chargeSource->auth_url = $faker->url;
        $chargeSource->publishing_status = ChargeSource::PUBLISHING_STATUS_PUBLIC;
        $chargeSource->cooperation_type = ChargeSource::COOPERATION_TYPE_POINT_SITE_API;
        $chargeSource->save();

        $this->loadApis(self::$chargeSource);
    }

    private function privatePointSite(Faker\Generator $faker)
    {
        //ポイントサイトの登録
        $chargeSource = new ChargeSource();
        $chargeSource->charge_source_code = self::$privatePointSite;
        $chargeSource->site_name = self::$chargeSourceName;
        $chargeSource->min_value = 300;
        $chargeSource->url = $faker->url;
        $chargeSource->icon_image_url = $faker->imageUrl();
        $chargeSource->denomination = 'pt';
        $chargeSource->introduce_charge_rate_point = 1;
        $chargeSource->introduce_charge_rate_price = 1;
        $chargeSource->description = $faker->text(100);
        $chargeSource->auth_url = $faker->url;
        $chargeSource->publishing_status = ChargeSource::PUBLISHING_STATUS_PRIVATE;
        $chargeSource->cooperation_type = ChargeSource::COOPERATION_TYPE_POINT_SITE_API;
        $chargeSource->save();

        $this->loadApis(self::$privatePointSite);
    }

    private function loadApis(string $chargeSourceCode)
    {
        // 交換
        $pointSiteApi = new PointSiteApi();
        $pointSiteApi->charge_source_code = $chargeSourceCode;
        $pointSiteApi->api_name = PointSiteApi::API_NAME_EXCHANGE;
        $pointSiteApi->url = self::$exchangeApiUrl;
        $pointSiteApi->publishing_status = PointSiteApi::PUBLISHING_STATUS_PUBLIC;
        $pointSiteApi->save();

        // 交換キャンセル
        $pointSiteApi = new PointSiteApi();
        $pointSiteApi->charge_source_code = $chargeSourceCode;
        $pointSiteApi->api_name = PointSiteApi::API_NAME_CANCEL_EXCHANGE;
        $pointSiteApi->url = self::$cancelExchangeApiUrl;
        $pointSiteApi->publishing_status = PointSiteApi::PUBLISHING_STATUS_PUBLIC;
        $pointSiteApi->save();

        // ポイント数取得
        $pointSiteApi = new PointSiteApi();
        $pointSiteApi->charge_source_code = $chargeSourceCode;
        $pointSiteApi->api_name = PointSiteApi::API_NAME_FETCH_POINT;
        $pointSiteApi->url = self::$pointApiUrl;
        $pointSiteApi->publishing_status = PointSiteApi::PUBLISHING_STATUS_PUBLIC;
        $pointSiteApi->save();
    }
}
