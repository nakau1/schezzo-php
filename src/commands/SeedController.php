<?php
namespace app\commands;

use app\components\GeneralChargeBonus;
use app\models\ChargeRequestHistory;
use app\models\ChargeSource;
use app\models\PointSiteApi;
use app\models\PolletUser;
use Faker\Factory;
use Faker\Generator;
use Yii;
use yii\base\ErrorException;
use yii\console\Controller;
use yii\db\ActiveRecord;
use yii\db\IntegrityException;
use yii\helpers\FileHelper;

/**
 * 初期データを投入する
 * TODO: 見通しが悪いのでファイル分割する
 *
 * Class SeedController
 * @package app\commands
 */
class SeedController extends Controller
{
    /** @var Generator */
    private $faker;

    public function init()
    {
        parent::init();

        $this->validateEnvironment();
        $this->faker = Factory::create();
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

    /**
     * データをすべて削除する
     *
     * @throws \yii\base\NotSupportedException
     * @throws \yii\db\Exception
     */
    public function actionClear()
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
        // ファイルをすべて削除する
        foreach (FileHelper::findFiles(Yii::$app->params['hulftPath']) as $path) {
            unlink($path);
        };
    }

    /**
     * demoに使うデータの生成
     */
    public function actionIndex()
    {
        $this->actionClear();

        $dummyPointSite = $this->setDummyPointSite();
        $this->setRehearsalHapitas();
        $this->setPointIncome();

        $this->setNewUser(100000);

        $user = $this->setWaitingIssueUser(100001);
        $this->setFirstChargeRequest($user, $dummyPointSite);
        $user = $this->setWaitingIssueUser(100002);
        $this->setFirstChargeRequest($user, $dummyPointSite);
        $user = $this->setWaitingIssueUser(100003);
        $this->setFirstChargeRequest($user, $dummyPointSite);
        $user = $this->setWaitingIssueUser(100004);
        $this->setFirstChargeRequest($user, $dummyPointSite);
        $user = $this->setWaitingIssueUser(100005);
        $this->setFirstChargeRequest($user, $dummyPointSite);

        $issuedUser = $this->setIssuedUser(100006);

        // セディナシステムのテスト環境に存在するセディナIDを指定
        $this->setActivatedUser(100007, '0002809690550944');
        $this->setActivatedUser(100008, '0002971879008704');
        $this->setActivatedUser(100009, '0002062728200989');
        $this->setActivatedUser(100010, '0002482088151970');
        $this->setActivatedUser(100011, '0002901448102969');
        $this->setActivatedUser(100012, '0002365778470224');
        $this->setActivatedUser(100013, '0002785138421213');
        $this->setActivatedUser(100014, '0002249468788477');
        $this->setActivatedUser(100015, '0002668828739468');
        $this->setActivatedUser(100016, '0002133159106720');
        $this->setActivatedUser(100017, '0002552519057713');

        $activatedUser = $this->setActivatedUser(100100, '0002274020918206');
        $this->outputUserInfo($activatedUser);

        // セディナさんがアプリ閲覧用に使うアカウント
        $this->setActivatedUserForCedynaTest();

        // 発番通知ファイル
        $this->makeReceivedNumberedCedynaIdFile(
            [100001, 100002, 100003],
            [
                $this->faker->regexify('[0-9]{16}'),
                $this->faker->regexify('[0-9]{16}'),
                $this->faker->regexify('[0-9]{16}'),
            ]
        );

        // 入金結果ファイル
        $this->makeReceivedPaymentFile([
            $this->setRequestedChargeRequest($issuedUser, $dummyPointSite),
            $this->setRequestedChargeRequest($activatedUser, $dummyPointSite),
            $this->setRequestedChargeRequest($activatedUser, $dummyPointSite),
        ], [
            $this->setRequestedChargeRequest($activatedUser, $dummyPointSite),
        ]);
        // 結果待ち
        $this->setRequestedChargeRequest($activatedUser, $dummyPointSite);
    }

    /**
     * demo用のポイントサイトをチャージ元として設定する
     */
    private function setDummyPointSite()
    {
        $chargeSource = new ChargeSource();
        $chargeSource->charge_source_code = 'demodemo';
        $chargeSource->api_key = 'demodemodemodemo';
        $chargeSource->site_name = 'デモ用ポイントサイト';
        $chargeSource->min_value = 1;
        $chargeSource->card_issue_fee = 0;
        $chargeSource->url = 'http://polletcorp.com';
        $chargeSource->icon_image_url = '/img/img_rogo_dummy.png';
        $chargeSource->denomination = 'ポイント';
        $chargeSource->introduce_charge_rate_point = 1;
        $chargeSource->introduce_charge_rate_price = 1;
        $chargeSource->description = 'ハピタスは「楽天」や「ヤフオク」など2,000社以上と提携。提携先の利用でポイントが貯まって、無料で現金やギフト券に交換できます。';
        $chargeSource->auth_url = '/demo/authenticate';
        $chargeSource->publishing_status = ChargeSource::PUBLISHING_STATUS_PUBLIC;
        $chargeSource->cooperation_type = ChargeSource::COOPERATION_TYPE_POINT_SITE_API;
        $chargeSource->save();

        $this->setDummyPointSiteApis($chargeSource);

        return $chargeSource;
    }

    /**
     * ポイントサイトのAPI
     * @param ChargeSource $pointSite
     */
    private function setDummyPointSiteApis(ChargeSource $pointSite)
    {
        $pointSiteApi = new PointSiteApi();
        $pointSiteApi->charge_source_code = $pointSite->charge_source_code;
        $pointSiteApi->api_name = PointSiteApi::API_NAME_EXCHANGE;
        $pointSiteApi->url = 'https://demoapp.pollet.me/demo/api-exchange';
        $pointSiteApi->publishing_status = PointSiteApi::PUBLISHING_STATUS_PUBLIC;
        $pointSiteApi->save();

        $pointSiteApi = new PointSiteApi();
        $pointSiteApi->charge_source_code = $pointSite->charge_source_code;
        $pointSiteApi->api_name = PointSiteApi::API_NAME_CANCEL_EXCHANGE;
        $pointSiteApi->url = 'https://demoapp.pollet.me/demo/api-cancel-exchange';
        $pointSiteApi->publishing_status = PointSiteApi::PUBLISHING_STATUS_PUBLIC;
        $pointSiteApi->save();

        $pointSiteApi = new PointSiteApi();
        $pointSiteApi->charge_source_code = $pointSite->charge_source_code;
        $pointSiteApi->api_name = PointSiteApi::API_NAME_FETCH_POINT;
        $pointSiteApi->url = 'https://demoapp.pollet.me/demo/api-point';
        $pointSiteApi->publishing_status = PointSiteApi::PUBLISHING_STATUS_PUBLIC;
        $pointSiteApi->save();

        $pointSiteApi = new PointSiteApi();
        $pointSiteApi->charge_source_code = $pointSite->charge_source_code;
        $pointSiteApi->api_name = PointSiteApi::API_NAME_REQUEST_TOKEN;
        $pointSiteApi->url = 'https://demoapp.pollet.me/demo/api-token';
        $pointSiteApi->publishing_status = PointSiteApi::PUBLISHING_STATUS_PUBLIC;
        $pointSiteApi->save();
    }

    /**
     * ハピタスをdemo環境にチャージ元として設定する
     */
    private function setRehearsalHapitas()
    {
        //ハピタスがすでに登録してあればデータ作成せずに終了
        $hapitas = ChargeSource::findOne(['charge_source_code' => 'hapitas']);
        if (!is_null($hapitas)) {
            return $hapitas;
        }

        // ハピタスRH環境
        $chargeSource = new ChargeSource();
        $chargeSource->charge_source_code = 'hapitas';
        $chargeSource->api_key = '';
        $chargeSource->site_name = 'ハピタス';
        $chargeSource->min_value = 1;
        $chargeSource->card_issue_fee = 0;
        $chargeSource->url = 'http://rh.hapitas.jp';
        $chargeSource->icon_image_url = '/img/logo_hapitas.png';
        $chargeSource->denomination = 'ポイント';
        $chargeSource->introduce_charge_rate_point = 1;
        $chargeSource->introduce_charge_rate_price = 1;
        $chargeSource->description = 'ハピタスは「楽天」や「ヤフオク」など2,000社以上と提携。提携先の利用でポイントが貯まって、無料で現金やギフト券に交換できます。';
        $chargeSource->auth_url = 'https://rhsp.hapitas.jp/auth/pollet';
        $chargeSource->publishing_status = ChargeSource::PUBLISHING_STATUS_PUBLIC;
        $chargeSource->cooperation_type = ChargeSource::COOPERATION_TYPE_POINT_SITE_API;
        $chargeSource->save();

        $this->setHapitasApis($chargeSource);

        return $chargeSource;
    }

    /**
     * ハピタスのAPI
     * @param ChargeSource $chargeSource
     */
    private function setHapitasApis(ChargeSource $chargeSource)
    {
        //リハーサル環境に接続する
        $pointSiteApi = new PointSiteApi();
        $pointSiteApi->charge_source_code = $chargeSource->charge_source_code;
        $pointSiteApi->api_name = PointSiteApi::API_NAME_EXCHANGE;
        $pointSiteApi->url = 'https://rhpollet-api.hapitas.jp/exchange';
        $pointSiteApi->publishing_status = PointSiteApi::PUBLISHING_STATUS_PUBLIC;
        $pointSiteApi->save();

        $pointSiteApi = new PointSiteApi();
        $pointSiteApi->charge_source_code = $chargeSource->charge_source_code;
        $pointSiteApi->api_name = PointSiteApi::API_NAME_CANCEL_EXCHANGE;
        $pointSiteApi->url = 'https://rhpollet-api.hapitas.jp/exchange';
        $pointSiteApi->publishing_status = PointSiteApi::PUBLISHING_STATUS_PUBLIC;
        $pointSiteApi->save();

        $pointSiteApi = new PointSiteApi();
        $pointSiteApi->charge_source_code = $chargeSource->charge_source_code;
        $pointSiteApi->api_name = PointSiteApi::API_NAME_FETCH_POINT;
        $pointSiteApi->url = 'https://rhpollet-api.hapitas.jp/point';
        $pointSiteApi->publishing_status = PointSiteApi::PUBLISHING_STATUS_PUBLIC;
        $pointSiteApi->save();

        $pointSiteApi = new PointSiteApi();
        $pointSiteApi->charge_source_code = $chargeSource->charge_source_code;
        $pointSiteApi->api_name = PointSiteApi::API_NAME_REQUEST_TOKEN;
        $pointSiteApi->url = 'https://rhpollet-api.hapitas.jp/token';
        $pointSiteApi->publishing_status = PointSiteApi::PUBLISHING_STATUS_PUBLIC;
        $pointSiteApi->save();
    }

    /**
     * ポイントインカムをdemo環境にチャージ元として設定する
     */
    private function setPointIncome()
    {
        //ハピタスがすでに登録してあればデータ作成せずに終了
        $hapitas = ChargeSource::findOne(['charge_source_code' => 'pointi']);
        if (!is_null($hapitas)) {
            return $hapitas;
        }

        // ハピタスRH環境
        $chargeSource = new ChargeSource();
        $chargeSource->charge_source_code = 'pointi';
        $chargeSource->api_key = '7j1TOPW66WnWj0mPxJI0VaRpoGUp4AN9GcFhBY9SuSn9CLrnaCI2j7ELSngITcPe';
        $chargeSource->site_name = 'ポイントインカム';
        $chargeSource->min_value = 500;
        $chargeSource->card_issue_fee = 0;
        $chargeSource->url = 'http://sp.pointi.jp';
        $chargeSource->icon_image_url = '/img/img_rogo_dummy.png';
        $chargeSource->denomination = 'ポイント';
        $chargeSource->introduce_charge_rate_point = 5000;
        $chargeSource->introduce_charge_rate_price = 500;
        $chargeSource->description = 'ここにポイントインカムの説明文が入ります。';
        $chargeSource->auth_url = 'http://sp.pointi.jp';
        $chargeSource->publishing_status = ChargeSource::PUBLISHING_STATUS_PUBLIC;
        $chargeSource->cooperation_type = ChargeSource::COOPERATION_TYPE_POLLET_API;
        $chargeSource->save();

        return $chargeSource;
    }


    /**
     * @param int $polletId
     * @return PolletUser
     */
    private function setNewUser(int $polletId)
    {
        $user = $this->makeDefaultPolletUser($polletId);
        $user->registration_status = PolletUser::STATUS_NEW_USER;
        $user->cedyna_id = null;
        $user->rawPassword = null;
        $user->save();

        return $user;
    }

    /**
     * @param int $polletId
     * @return PolletUser
     */
    private function setWaitingIssueUser(int $polletId)
    {
        $user = $this->makeDefaultPolletUser($polletId);
        $user->registration_status = PolletUser::STATUS_WAITING_ISSUE;
        $user->cedyna_id = null;
        $user->rawPassword = null;
        $user->save();

        return $user;
    }

    /**
     * @param int $polletId
     * @return PolletUser
     */
    private function setIssuedUser(int $polletId, $cedynaId = null)
    {
        $user = $this->makeDefaultPolletUser($polletId);
        $user->registration_status = PolletUser::STATUS_ISSUED;
        $user->rawPassword = null;
        if (!is_null($cedynaId)) {
            $user->cedyna_id = $cedynaId;
        }
        $user->save();

        return $user;
    }

    /**
     * @param int $polletId
     * @param string $cedynaId
     * @return PolletUser
     */
    private function setActivatedUser(int $polletId, string $cedynaId)
    {
        $user = $this->makeDefaultPolletUser($polletId);
        $user->registration_status = PolletUser::STATUS_ACTIVATED;
        $user->cedyna_id = $cedynaId;
        $user->rawPassword = 'Passw0rd';
        $user->save();

        return $user;
    }

    /**
     * @param int $polletId
     * @return PolletUser
     */
    private function makeDefaultPolletUser(int $polletId)
    {
        $user = new PolletUser();

        $user->id = $polletId;
        $user->user_code_secret = Yii::$app->security->generateRandomString() . '_' . time();
        $user->cedyna_id = $this->faker->regexify('[0-9]{10}');
        $user->rawPassword = $this->faker->password();
        $user->mail_address = $this->faker->email;
        $user->balance_at_charge = 0;

        return $user;
    }

    /**
     * 発番通知前の初回チャージ申請履歴データを生成
     *
     * @param PolletUser $user
     * @param ChargeSource $chargeSource
     *
     * @return ChargeRequestHistory
     */
    private function setFirstChargeRequest(PolletUser $user, ChargeSource $chargeSource)
    {
        $chargeRequest = $this->makeDefaultChargeRequest($user, $chargeSource);
        $chargeRequest->charge_value = $chargeRequest->exchange_value - $chargeSource->card_issue_fee;
        $chargeRequest->processing_status = ChargeRequestHistory::STATUS_UNPROCESSED_FIRST_CHARGE;
        $chargeRequest->save();

        return $chargeRequest;
    }

    /**
     * @param PolletUser $user
     * @param ChargeSource $chargeSource
     * @return ChargeRequestHistory
     */
    private function setRequestedChargeRequest(PolletUser $user, ChargeSource $chargeSource)
    {
        $chargeRequest = $this->makeDefaultChargeRequest($user, $chargeSource);
        $chargeRequest->processing_status = ChargeRequestHistory::STATUS_REQUESTED_CHARGE;
        $chargeRequest->save();

        return $chargeRequest;
    }

    /**
     * @param PolletUser $user
     * @param ChargeSource $chargeSource
     * @param int $exchangeValue
     * @param int $id
     * @return ChargeRequestHistory
     */
    private function setReadyChargeRequest(
        PolletUser $user,
        ChargeSource $chargeSource,
        int $exchangeValue,
        int $id = null
    ) {
        $chargeRequest = $this->makeDefaultChargeRequest($user, $chargeSource);
        if (!is_null($id)) {
            $chargeRequest->id = $id;
        }
        $chargeRequest->exchange_value = $exchangeValue;
        // ボーナスポイントを追加してチャージ
        $chargeRequest->charge_value = (new GeneralChargeBonus())->applyTo($chargeRequest->exchange_value);
        $chargeRequest->processing_status = ChargeRequestHistory::STATUS_READY;
        $chargeRequest->save();

        return $chargeRequest;
    }

    /**
     * @param PolletUser $user
     * @param ChargeSource $chargeSource
     * @return ChargeRequestHistory
     */
    private function makeDefaultChargeRequest(PolletUser $user, ChargeSource $chargeSource)
    {
        $chargeRequestHistory = new ChargeRequestHistory();
        $chargeRequestHistory->pollet_user_id = $user->id;
        $chargeRequestHistory->charge_source_code = $chargeSource->charge_source_code;
        $chargeRequestHistory->charge_value = $this->faker->numberBetween(5, 100) * 100;
        $chargeRequestHistory->exchange_value = $chargeRequestHistory->charge_value;
        $chargeRequestHistory->processing_status = $chargeRequestHistory::STATUS_APPLIED_CHARGE;
        $chargeRequestHistory->cause = $chargeSource->site_name . 'からチャージ（0.5％込）';

        return $chargeRequestHistory;
    }

    /**
     * @param array $polletIds
     * @param array $cedynaIds
     */
    private function makeReceivedNumberedCedynaIdFile(array $polletIds, array $cedynaIds)
    {
        $receivedFilesDirectory = Yii::$app->params['hulftPath'] . '/recv';
        $retryDirectory = Yii::$app->params['hulftPath'] . '/app/receive_numbered_cedyna_id/retry';
        $processingDirectory = Yii::$app->params['hulftPath'] . '/app/receive_numbered_cedyna_id/processing';
        $completeDirectory = Yii::$app->params['hulftPath'] . '/app/receive_numbered_cedyna_id/complete';

        $this->makeDirectoryIfNotExists($receivedFilesDirectory);
        $this->makeDirectoryIfNotExists($retryDirectory);
        $this->makeDirectoryIfNotExists($processingDirectory);
        $this->makeDirectoryIfNotExists($completeDirectory);

        $csv = '"S","' . $this->faker->dateTime->format('Y/m/d H:i:s') . '"' . "\n";
        foreach (array_combine($polletIds, $cedynaIds) as $polletId => $cedynaId) {
            $formattedPolletId = sprintf('%016d', $polletId);
            $formattedCedynaId = sprintf('%016d', $cedynaId);
            $csv .= '"D","409336123456789000","' . $formattedCedynaId . '","' . $formattedPolletId . '","abcd","20160801","20160805","20"' . "\n";
        }
        $csv .= '"E",' . sprintf('%8s', count($polletIds)) . "\n";
        $sjisCsv = mb_convert_encoding($csv, 'SJIS');
        file_put_contents($receivedFilesDirectory . '/scdpol02.txt', $sjisCsv);
    }

    /**
     * @param ChargeRequestHistory[] $okChargeRequests
     * @param ChargeRequestHistory[] $errorChargeRequests
     */
    private function makeReceivedPaymentFile(array $okChargeRequests, array $errorChargeRequests)
    {
        $receivedFilesDirectory = Yii::$app->params['hulftPath'] . '/recv';
        $retryDirectory = Yii::$app->params['hulftPath'] . '/app/receive_payment_file/retry';
        $processingDirectory = Yii::$app->params['hulftPath'] . '/app/receive_payment_file/processing';
        $completeDirectory = Yii::$app->params['hulftPath'] . '/app/receive_payment_file/complete';

        $this->makeDirectoryIfNotExists($receivedFilesDirectory);
        $this->makeDirectoryIfNotExists($retryDirectory);
        $this->makeDirectoryIfNotExists($processingDirectory);
        $this->makeDirectoryIfNotExists($completeDirectory);

        $csv = '"S","' . $this->faker->dateTime->format('Y/m/d H:i:s') . '"' . "\n";
        $csv .= '"H","入金種別","イシュアコード","提携先コード","カード種別区分","会員グループ番号","会員番号","カードID","入金額","加盟店名（チャージ理由）","処理結果","エラーコード","処理番号",""' . "\n";
        foreach ($okChargeRequests as $chargeRequest) {
            $cedynaId = sprintf('%016d', $chargeRequest->polletUser->cedyna_id);
            $csv .= '"D","0421","CEDYNA","提携先ごとに採番","0001xxxx","' . $cedynaId . '","' . $cedynaId . '","","' . $chargeRequest->charge_value . '","' . $chargeRequest->cause . '","0","","' . $chargeRequest->id . '"' . "\n";
        }
        foreach ($errorChargeRequests as $chargeRequest) {
            $cedynaId = sprintf('%016d', $chargeRequest->polletUser->cedyna_id);
            $csv .= '"D","0421","CEDYNA","提携先ごとに採番","0001xxxx","' . $cedynaId . '","' . $cedynaId . '","","' . $chargeRequest->charge_value . '","' . $chargeRequest->cause . '","1","4155","' . $chargeRequest->id . '"' . "\n";
        }
        $csv .= '"E",' . sprintf('%8s', count($okChargeRequests) + count($errorChargeRequests)) . "\n";
        $sjisCsv = mb_convert_encoding($csv, 'SJIS');
        file_put_contents($receivedFilesDirectory . '/scdpol01.txt', $sjisCsv);
    }

    /**
     * @param string $path
     * @throws \yii\base\Exception
     */
    private function makeDirectoryIfNotExists(string $path)
    {
        if (!file_exists($path)) {
            FileHelper::createDirectory($path);
        }
    }

    /**
     * ユーザー情報を標準出力
     * @param PolletUser $user
     */
    private function outputUserInfo($user)
    {
        echo '-- user information --' . PHP_EOL;
        echo 'id        : ' . $user->id . PHP_EOL;
        echo 'pollet_id : ' . $user->user_code_secret . PHP_EOL;
        echo 'cedyna_id : ' . $user->cedyna_id . PHP_EOL;
        echo 'password  : ' . $user->rawPassword . PHP_EOL;
        echo 'status    : ' . $user->registration_status . PHP_EOL;
    }

    /**
     * セディナさんがアプリ閲覧用に使う検証環境アカウント
     *
     */
    private function setActivatedUserForCedynaTest()
    {
        $userData = [
            0 => ['polletId' => 999990, 'cedynaId' => '0002221691175293'],
            1 => ['polletId' => 999991, 'cedynaId' => '0002338000857047'],
            2 => ['polletId' => 999992, 'cedynaId' => '0002641051126281'],
            3 => ['polletId' => 999993, 'cedynaId' => '0002757360808037'],
            4 => ['polletId' => 999994, 'cedynaId' => '0002873670489788'],
        ];

        foreach ($userData as $key => $value) {
            $this->setActivatedUser($value['polletId'], $value['cedynaId']);
        }
    }

    /**
     *セディナ入金ファイル伝送テスト用データ 9-1-1 正常系1件チャージ
     * https://docs.google.com/spreadsheets/d/1sVSwu4klwXGOxxpzBgdE24AOtjodOCT1ubzuMjJJN6c/edit#gid=2060013897
     *
     */
    public function actionCedyna911()
    {
        $this->actionClear();

        $hapitas = $this->setRehearsalHapitas();
        $users = $this->setUserForCedynaPaymentTest();
        $this->setReadyChargeRequest($users['shiro'], $hapitas, 1000, 1000010001);
    }


    /**
     * セディナ入金ファイル伝送テスト用データ 9-2-1 正常系複数件チャージ
     * https://docs.google.com/spreadsheets/d/1sVSwu4klwXGOxxpzBgdE24AOtjodOCT1ubzuMjJJN6c/edit#gid=2060013897
     *
     */
    public function actionCedyna921()
    {
        $this->actionClear();
        $hapitas = $this->setRehearsalHapitas();
        $users = $this->setUserForCedynaPaymentTest();

        $this->setReadyChargeRequest($users['shiro'], $hapitas, 1, 1000010002);
        $this->setReadyChargeRequest($users['goro'], $hapitas, 10000, 1000010003);
    }

    /**
     * セディナ入金ファイル伝送テスト用データ 9-2-2
     * レコード件数が複数件、加盟店名(チャージ理由)の桁数が最大のデータ
     * https://docs.google.com/spreadsheets/d/1sVSwu4klwXGOxxpzBgdE24AOtjodOCT1ubzuMjJJN6c/edit#gid=2060013897
     *
     */
    public function actionCedyna922()
    {
        $this->actionClear();
        $hapitas = $this->setRehearsalHapitas();
        $users = $this->setUserForCedynaPaymentTest();

        $this->setReadyChargeRequestOfMaxByteCause($users['shiro'], $hapitas, 1, 1000010004);
        $this->setReadyChargeRequestOfMaxByteCause($users['goro'], $hapitas, 1, 1000010005);
        $this->setReadyChargeRequestOfMaxByteCause($users['rokuko'], $hapitas, 1, 1000010006);
    }

    /**
     * セディナ入金ファイル伝送テスト用データ 9-2-3
     * レコード件数が複数件の入金データ、そのうち１件はカード状態不正（無効）の会員。
     * https://docs.google.com/spreadsheets/d/1sVSwu4klwXGOxxpzBgdE24AOtjodOCT1ubzuMjJJN6c/edit#gid=2060013897
     *
     */
    public function actionCedyna923()
    {
        $this->actionClear();
        $hapitas = $this->setRehearsalHapitas();
        $users = $this->setUserForCedynaPaymentTest();

        $this->setReadyChargeRequest($users['rokuko'], $hapitas, 10000, 1000010007);
        $this->setReadyChargeRequest($users['kyumi'], $hapitas, 10000, 1000010008);
    }

    /**
     * セディナ入金ファイル伝送テスト用データ 9-2-4
     * レコード件数が複数件の入金データで、そのうち１件はカード状態不正（有効期限切れ）の会員が含まれているデータを受信する。
     * https://docs.google.com/spreadsheets/d/1sVSwu4klwXGOxxpzBgdE24AOtjodOCT1ubzuMjJJN6c/edit#gid=2060013897
     *
     */
    public function actionCedyna924()
    {
        $this->actionClear();
        $hapitas = $this->setRehearsalHapitas();
        $users = $this->setUserForCedynaPaymentTest();

        $this->setReadyChargeRequest($users['goro'], $hapitas, 10000, 1000010009);
        //カードステータスはセディナさんがテスト時に手動で変更する
        $this->setReadyChargeRequest($users['nanako'], $hapitas, 10000, 1000010010);

    }

    /**
     * セディナ入金ファイル伝送テスト用データ 9-2-5
     * レコード件数が複数件の入金データで、そのうち１件は残高が999,900円会員に対して「101」以上の入金額が設定したデータを受信する。
     * https://docs.google.com/spreadsheets/d/1sVSwu4klwXGOxxpzBgdE24AOtjodOCT1ubzuMjJJN6c/edit#gid=2060013897
     *
     */
    public function actionCedyna925()
    {
        $this->actionClear();
        $hapitas = $this->setRehearsalHapitas();
        $users = $this->setUserForCedynaPaymentTest();

        $this->setReadyChargeRequest($users['shiro'], $hapitas, 10000, 1000010011);
        $this->setReadyChargeRequest($users['goro'], $hapitas, 10000, 1000010012);
        //カード残高をはセディナさんがテスト時に手動で999,900円に変更する
        $this->setReadyChargeRequest($users['hachie'], $hapitas, 101, 1000010013);
    }

    /**
     * セディナ入金ファイル伝送テスト用データ 9-3-1
     * カード種別区分下4桁の経路IDが"0002"（異常）のレコード１件のみのデータを受信する
     * 経路IDはDBで管理している値ではないので、バッチで出力したファイルを手動で0002に書き換える
     * https://docs.google.com/spreadsheets/d/1uYuFqe6PqiOiVpiRWqGY-fVMqXxV3Bpbxn-OjuA4dQU/edit#gid=537450438
     */
    public function actionCedyna931()
    {
        $this->actionClear();
        $hapitas = $this->setRehearsalHapitas();
        $users = $this->setUserForCedynaPaymentTest();

        $this->setReadyChargeRequest($users['jiro'], $hapitas, 10000, 1000010014);
    }

    /**
     * セディナ入金ファイル伝送テスト用データ 9-3-2
     * カード種別区分下4桁の経路IDが"9999"（異常）のレコード複数のデータを受信する
     * 経路IDはDBで管理している値ではないので、バッチで出力したファイルを手動で9999に書き換える
     * https://docs.google.com/spreadsheets/d/1uYuFqe6PqiOiVpiRWqGY-fVMqXxV3Bpbxn-OjuA4dQU/edit#gid=537450438
     */
    public function actionCedyna932()
    {
        $this->actionClear();
        $hapitas = $this->setRehearsalHapitas();
        $users = $this->setUserForCedynaPaymentTest();

        $this->setReadyChargeRequest($users['shiro'], $hapitas, 10000, 1000010015);
        $this->setReadyChargeRequest($users['goro'], $hapitas, 10000, 1000010016);
    }
    /**
     * セディナ入金ファイル伝送テスト用データ 9-3-3
     * 入金種別が"9999"（異常）のレコード複数のデータを受信する
     * 経路IDはDBで管理している値ではないので、バッチで出力したファイルを手動で9999に書き換える
     * https://docs.google.com/spreadsheets/d/1uYuFqe6PqiOiVpiRWqGY-fVMqXxV3Bpbxn-OjuA4dQU/edit#gid=537450438
     */
    public function actionCedyna933()
    {
        $this->actionClear();
        $hapitas = $this->setRehearsalHapitas();
        $users = $this->setUserForCedynaPaymentTest();

        $this->setReadyChargeRequest($users['rokuko'], $hapitas, 10000, 1000010017);
        $this->setReadyChargeRequest($users['nanako'], $hapitas, 10000, 1000010018);
    }

    /**
     * セディナ入金ファイル伝送テスト用データ 9-3-4
     * 処理額がマイナス（異常）のレコードのみ複数件のデータを受信する
     * ChargeRequestHistoryクラスの定義に0以下のデータ作成を許容しない制限がかかっているのでファイル出力後手動でマイナスの数字に書き換えをおこなう
     * https://docs.google.com/spreadsheets/d/1uYuFqe6PqiOiVpiRWqGY-fVMqXxV3Bpbxn-OjuA4dQU/edit#gid=537450438
     */
    public function actionCedyna934()
    {
        $this->actionClear();
        $hapitas = $this->setRehearsalHapitas();
        $users = $this->setUserForCedynaPaymentTest();
        // ファイル出力後手動でマイナスの数字に書き換える
        $this->setReadyChargeRequest($users['shiro'], $hapitas, 1 , 1000010019);
        $this->setReadyChargeRequest($users['goro'], $hapitas, 1, 1000010020);
    }
    /**
     * @param PolletUser $user
     * @param ChargeSource $chargeSource
     * @param int $exchangeValue
     * @param int $id
     * @return ChargeRequestHistory
     */
    private function setReadyChargeRequestOfMaxByteCause(
        PolletUser $user,
        ChargeSource $chargeSource,
        int $exchangeValue,
        int $id = null
    ) {
        $chargeRequest = $this->makeDefaultChargeRequest($user, $chargeSource);
        if (!is_null($id)) {
            $chargeRequest->id = $id;
        }
        $chargeRequest->exchange_value = $exchangeValue;
        // ボーナスポイントを追加してチャージ
        $chargeRequest->charge_value = (new GeneralChargeBonus())->applyTo($chargeRequest->exchange_value);
        //最大文字数20文字。UTF8で60Byteが上限
        $chargeRequest->cause = '加盟店名（チャージ理由）が最大文字数のテ';
        $chargeRequest->processing_status = ChargeRequestHistory::STATUS_READY;
        $chargeRequest->save();

        return $chargeRequest;
    }

    /**
     * セディナファイル連携　入金ファイルテスト用ユーザーデータ
     * テスト終わったら消していいメソッド。
     *
     * @return array
     */
    private function setUserForCedynaPaymentTest()
    {
        $users = [];
        $this->setRehearsalHapitas();

        //カードステータス有効
        $users['jiro']   = $this->setActivatedUser(100202, '0002506640281709');
        $users['shiro']  = $this->setActivatedUser(100204, '0002390330599955');
        $users['goro']   = $this->setActivatedUser(100205, '0002809690550944');
        $users['rokuko'] = $this->setActivatedUser(100206, '0002274020918206');
        $users['nanako'] = $this->setActivatedUser(100207, '0002016849424977');
        $users['hachie'] = $this->setActivatedUser(100208, '0002436209375967');
        //カードステータス無効
        $users['kyumi']  = $this->setActivatedUser(100209, '0002855569326957');

        return $users;
    }

    /**
     * セディナファイル連携　発番通知テスト用ユーザーデータ
     * テスト終わったら消していいメソッド。
     *
     */
    public function actionCedynaNumberData()
    {
        //テストケース7-2-1用
        $this->setWaitingIssueUser(24985740);
        //テストケース7-3-1用
        $this->setWaitingIssueUser(2147483647);
        //テストケース7-3-2用
        $this->setWaitingIssueUser(11111111);
        $this->setWaitingIssueUser(22222222);
        $this->setWaitingIssueUser(33333333);
        //テストケース7-4-2用
        $this->setWaitingIssueUser(44444444);
        //テストケース7-4-3用
        $this->setWaitingIssueUser(66666666);
        //テストケース7-5-1用
        $this->setWaitingIssueUser(88888888);
    }
}
