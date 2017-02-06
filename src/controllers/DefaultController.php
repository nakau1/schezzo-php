<?php
namespace app\controllers;

use app\Environment;
use app\helpers\Dispatcher;
use app\models\exceptions\InternalServerErrorHttpException;
use app\models\PolletUser;
use app\controllers\actions\ErrorAction;
use Yii;
use yii\filters\AccessControl;
use yii\web\NotAcceptableHttpException;
use yii\web\UnauthorizedHttpException;

/**
 * Class DefaultController
 * @package app\controllers
 */
class DefaultController extends CommonController
{
    /** チュートリアルが何度も表示されないようにするためのセッションキー */
    const SESSION_TUTORIAL_SHOWED_KEY = 'showed_once_tutorial';

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['index', 'login', 'error'],
                        'allow'   => true,
                        'roles'   => ['?'],
                    ],
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    public function actions()
    {
        return ['error' => ['class' => ErrorAction::className()]];
    }

    /**
     * 発行IDでのログインとページ振り分けを行う
     * @param string|null $app_operation
     * @return \yii\web\Response
     * @throws NotAcceptableHttpException
     * @throws UnauthorizedHttpException
     */
    public function actionIndex($app_operation = null)
    {
        // ヘッダーからユーザーを取得
        $user = PolletUser::find()->active()->userCodeSecret(Yii::$app->request->headers->get(self::HEADER_POLLET_ID))->one();
        if (!$user) {
            throw new NotAcceptableHttpException("エラー(406)が発生しました。\nこちらからお問い合わせください。");
        } else if (!Yii::$app->user->login($user)) {
            throw new UnauthorizedHttpException("エラー(401)が発生しました。\nアプリを再インストールするかこちらからお問い合わせください。");
        }

        if (isset($app_operation)) {
            return $this->redirect($app_operation);
        } else {
            return $this->redirect(Dispatcher::forIndex($user));
        }
    }

    /**
     * 1. はじめに(兼:チュートリアル)
     * @return string
     */
    public function actionStart()
    {
        $token = Yii::$app->session->get(self::SESSION_TUTORIAL_SHOWED_KEY);
        if ($token !== $this->authorizedUser->user_code_secret) {
            $token = null;
        }
        $showTutorial = is_null($token);
        Yii::$app->session->set(self::SESSION_TUTORIAL_SHOWED_KEY, $this->authorizedUser->user_code_secret);

        return $this->render('start', [
            'showTutorial' => $showTutorial,
        ]);
    }

    /**
     * 24-3. アプリ利用規約
     * @return string
     */
    public function actionTerms()
    {
        // スタートページからリンクされた場合はスタートページへ戻す
        $backAction = $this->authorizedUser->isNewUser() || $this->authorizedUser->isSignOut()
            ? ['start/']
            : ['guide/'];

        return $this->render('terms', [
            'backAction' => $backAction,
        ]);
    }

    /**
     * プライバシーポリシー
     * @return string
     */
    public function actionPrivacyPolicy()
    {
        // スタートページからリンクされた場合はスタートページへ戻す
        $backAction = $this->authorizedUser->isNewUser() || $this->authorizedUser->isSignOut()
            ? ['start/']
            : ['guide/'];

        return $this->render('privacy-policy', [
            'backAction' => $backAction,
        ]);
    }

    /**
     * カード利用規約
     * @return string
     */
    public function actionCardTerms()
    {
        return $this->render('card-terms');
    }

    /**
     * 25. 設定画面
     * @return string
     */
    public function actionSetting()
    {
        return $this->render('setting');
    }

    /**
     * JS側からサーバエラーを出すためのアクション
     * 400エラー画面を表示します
     * @throws InternalServerErrorHttpException
     */
    public function actionJsError()
    {
        $msg = 'サーバーエラーが発生しました。もう一度操作をやり直してください。';
        $env = Environment::get();
        if ($env['mode'] === 'dev') {
            $msg .= '<p>このエラーは、javascriptのAjax通信中にエラーが発生し、サーバ側のDefaultController::actionJsError()にリダイレクトされて表示されています。</p>';
            $msg .= '<p style="font-size: small">※このメッセージは開発時にのみ表示されます</p>';
        }
        throw new InternalServerErrorHttpException($msg);
    }
}
