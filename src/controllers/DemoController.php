<?php

namespace app\controllers;

use app\models\forms\DemoCedynaIdForm;
use app\models\forms\DemoPushNotify;
use app\models\PolletUser;
use app\models\TradingHistory;
use Carbon\Carbon;
use Faker;
use Yii;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\Response;

/**
 * Class DemoController
 *
 * 開発の便宜上作成したデモ画面です
 *
 * @package app\controllers
 */
class DemoController extends Controller
{
    /** cedyna-my-page用のセッションキー */
    const SESSION_KEY_MYPAGE_ROWS = 'demo-cedyna-my-page-rows';

    public $enableCsrfValidation = false;

    /**
     * 外部認証
     *
     * 本来は外部サイトへ遷移します
     *
     * @param string $state
     * @return string
     * @throws BadRequestHttpException
     */
    public function actionAuthenticate($state)
    {
        // 変な書き換えがおこるので無効にする
        Yii::$app->user->enableSession = false;

        $faker = Faker\Factory::create();

        return $this->render('authenticate', [
            'linkSp' => "pollet://point-site-auth/index?code={$faker->md5}&state={$state}&charge_source_code=demodemo",
            'linkDev' => "/point-site-auth/index?code={$faker->md5}&state={$state}&charge_source_code=demodemo",
        ]);
    }

    /**
     * 開発デモ用発番
     *
     * ログイン中のユーザに任意のセディナIDを発番して「発番済」ステータスにすることができます
     *
     * @return string|Response
     */
    public function actionIssue()
    {
        $formModel = new DemoCedynaIdForm();
        $formModel->scenario = DemoCedynaIdForm::SCENARIO_DEMO;

        /* @var $user PolletUser */
        $user = Yii::$app->user->identity;
        if ($formModel->load(Yii::$app->request->post()) && $formModel->issue($user)) {
            return $this->redirect(['/top']);
        }

        return $this->render('issue', [
            'formModel' => $formModel,
        ]);
    }

    /**
     * 開発デモ用セディナマイページメールアドレス入力画面
     *
     * @return string
     */
    public function actionCedynaSendEmail()
    {
        return $this->render('cedyna_send_email');
    }

    /**
     * 開発デモ用セディナマイページメールアドレス入力完了画面
     * メールアドレスが空の場合、完了画面の代わりに入力画面をレンダリングする
     *
     * @return string
     */
    public function actionCedynaSendEmailComplete()
    {
        $params = Yii::$app->request->post();
        $email = $params['ctl00$ctl00$MainContent$DetailContent$cDetail$mail$Dtm_mail_address$TextBox'] ?? '';
        $confirmation = $params['ctl00$ctl00$MainContent$DetailContent$cDetail$mail$Dtm_mail_address2$TextBox'] ?? '';
        if (filter_var($email, FILTER_VALIDATE_EMAIL) && $email === $confirmation) {
            return $this->render('cedyna_send_email_complete');
        } else {
            return $this->render('cedyna_send_email', [
                'error' => 'メールアドレスを正しく入力してください',
            ]);
        }
    }

    /**
     * 開発デモ用セディナログイン画面
     * 両方入力されててセディナIDが16桁だったらログイン成功とする
     *
     * @return string
     */
    public function actionCedynaLogin()
    {
        $params = Yii::$app->request->post();
        $cedynaId = $params['TbxMemberNumber'] ?? '';
        $password = $params['TbxPassword'] ?? '';
        // 両方入力されててセディナIDが16桁だったらログイン成功とする
        if (!empty($cedynaId) && !empty($password) && preg_match('/\A[0-9]{16}\z/', $cedynaId)) {
            return $this->redirect('/demo/cedyna-my-page');
        } else {
            return $this->render('cedyna_login');
        }
    }

    /**
     * 開発デモ用セディナマイページ画面
     *
     * @return string
     */
    public function actionCedynaMyPage()
    {
        // [cedynaId => アクティベート済かどうかのフラグ]
        //
        // false にした行にアクティベートボタンが付くので、
        // 必要に応じて増やしたり減らしたりしてください
        $rows = Yii::$app->session->get(self::SESSION_KEY_MYPAGE_ROWS, [
            '1234567890123456' => false,
            //'2345678901234567' => false,
            //'3456789012345678' => false,
            //'4567890123456789' => true,
            //'5678901234567890' => false,
            //'6789012345678901' => true,
            //'7890123456789012' => false,
            //'8901234567890123' => false,
        ]);

        // 全部trueになったら、セッションをリセット()
        $reset = true;
        foreach ($rows as $row) {
            if (!$row) {
                $reset = false;
            }
        }
        if ($reset) {
            Yii::$app->session->remove(self::SESSION_KEY_MYPAGE_ROWS);
        } else {
            Yii::$app->session->set(self::SESSION_KEY_MYPAGE_ROWS, $rows);
        }

        return $this->render('cedyna_my_page', [
            'rows' => $rows,
            'cardValue' => Faker\Factory::create()->numberBetween(300, 1000000),
        ]);
    }

    /**
     * 開発デモ用セディナマイページ画面:アクティベートボタン用アクション
     * @param string $cardId カードID
     * @return string
     */
    public function actionCedynaMyPageActivate($cardId)
    {
        $rows = Yii::$app->session->get(self::SESSION_KEY_MYPAGE_ROWS);
        if (!$rows || !isset($rows[$cardId])) {
            return '';
        }

        $rows[$cardId] = true;
        Yii::$app->session->set(self::SESSION_KEY_MYPAGE_ROWS, $rows);
        return '';
    }

    /**
     * 開発デモ用セディナ取引履歴画面
     *
     * @return string
     */
    public function actionCedynaTradingHistory()
    {
        $params           = Yii::$app->request->post();
        $cardNumber       = $params['ctl00$ctl00$MainContent$SearchContent$cSearch$Srh_card_id$ddlList'] ?? '';
        $fromDate         = $params['ctl00$ctl00$MainContent$SearchContent$cSearch$Srh_transaction_datetime$TextBox'] ?? '';
        $toDate           = $params['ctl00$ctl00$MainContent$SearchContent$cSearch$Srh_transaction_datetime2$TextBox'] ?? '';
        $tradingHistories = [];

        if (!empty($fromDate) && !empty($toDate)) {
            $faker = Faker\Factory::create();

            $count = $faker->numberBetween(1, 5);
            for ($i = 0; $i < $count; $i++) {
                $history               = new TradingHistory();
                $history->shop         = $faker->text(60);
                $history->spentValue   = $faker->numberBetween(10, 5000);
                $history->tradingDate  = Carbon::instance($faker->dateTimeBetween($fromDate, $toDate));
                $tradingHistories[]    = $history;
            }
            // 利用日の降順にする
            usort($tradingHistories, function (TradingHistory $a, TradingHistory $b) {
                return $a->tradingDate < $b->tradingDate;
            });
        }

        return $this->render('cedyna_trading_history', [
            'cardNumber'       => $cardNumber,
            'fromDate'         => $fromDate,
            'toDate'           => $toDate,
            'tradingHistories' => $tradingHistories,
        ]);
    }

    /**
     * @param string $view
     * @param array  $params
     * @return string
     */
    public function render($view, $params = [])
    {
        $this->layout = 'simple';
        return parent::render($view, $params);
    }

    /**
     * demo用ポイントサイトアクセストークン発行URL
     */
    public function actionApiToken()
    {
        $faker = Faker\Factory::create();
        $accessToken = $faker->regexify('[0-9]{16}');
        $response = json_encode(['token' => $accessToken]);
        header('Content-Type: application/json');
        header('X-Content-Type-Options: nosniff');
        echo $response;
    }
    /**
     * demo用ポイントサイト交換API
     */
    public function actionApiExchange()
    {
        header('Content-Type: application/json');
        header('X-Content-Type-Options: nosniff');
        echo '{}';
    }
    /**
     * demo用ポイントサイト交換キャンセルAPI
     */
    public function actionApiCancelExchange()
    {
        header('Content-Type: application/json');
        header('X-Content-Type-Options: nosniff');
        echo '{}';
    }
    /**
     * demo用ポイントサイトポイント数取得API
     */
    public function actionApiPoint()
    {
        header('Content-Type: application/json');
        header('X-Content-Type-Options: nosniff');
        echo '{"valid_value": 700000}';
    }

    /**
     * ポイントサイトのアクセストークンを削除する
     *
     * @return Response
     * @throws \Exception
     */
    public function actionRemovePointSiteTokens()
    {
        /* @var $user PolletUser */
        $user = Yii::$app->user->identity;
        foreach ($user->pointSiteTokens as $token) {
            $token->delete();
        }

        return $this->redirect('/charge/list');
    }

    /**
     * プッシュ通知送信テストページ
     * @return string
     */
    public function actionPushTest()
    {
        $formModel = new DemoPushNotify();
        if ($formModel->load(Yii::$app->request->post())) {
            $formModel->send();
        }
        return $this->render('push-test', [
            'formModel' => $formModel,
        ]);
    }

    /**
     * 自分のデータを削除する
     *
     * @return Response
     * @throws \Exception
     */
    public function actionDeleteMe()
    {
        /* @var $user PolletUser */
        $user = Yii::$app->user->identity;
        if ($user) {
            $user->delete();
        }
        Yii::$app->session->destroy();

        echo 'データを削除しました。アプリを再起動してください。';
    }
}
