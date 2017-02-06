<?php
namespace app\controllers;

use app\helpers\Dispatcher;
use app\models\PolletUser;
use app\views\View;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;

/**
 * Class CommonController
 * @package app\controllers
 *
 * @property PolletUser $authorizedUser 認証されているユーザ
 */
class CommonController extends Controller
{
    /** 独自ヘッダキー */
    const HEADER_POLLET_ID = 'X-Pollet-Id';

    /** サインイン失敗時にセッションに残すキー */
    const SESSION_FAILED_KEY = 'failed-sign-in';

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
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function render($view, $params = [])
    {
        /** @var $viewObject View */
        $viewObject = $this->view;
        $viewObject->user = $this->authorizedUser;

        return parent::render($view, $params);
    }

    /**
     * 認証中のユーザのChargeValueが、
     * セディナ認証失敗で取得できない場合にログイン画面へリダイレクトさせる
     */
    protected function redirectIfNoneChargedValue()
    {
        if ($this->authorizedUser->myChargedValue === false) {
            // セディナ認証失敗はログインへリダイレクト
            Yii::$app->session->set(self::SESSION_FAILED_KEY, '1');
            $this->redirect(['auth/sign-in']);
        }
    }

    /**
     * @inheritdoc
     */
    public function goHome()
    {
        return Yii::$app->getResponse()->redirect(Dispatcher::forIndex($this->authorizedUser));
    }

    /**
     * @return null|PolletUser
     */
    public function getAuthorizedUser()
    {
        return Yii::$app->user->identity;
    }
}