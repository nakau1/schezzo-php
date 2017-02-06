<?php

namespace app\views;

use app\Environment;
use app\helpers\Dispatcher;
use app\models\PolletUser;
use app\views\traits\ViewTrait;

class View extends \yii\web\View
{
    use ViewTrait;

    const JS_VOID = 'javascript:void(0)';

    /** @var PolletUser アクセスしているユーザ */
    public $user;

    /** @var bool 背景をグレーにするかどうか */
    public $isGrayBackground = true;

    /** @var bool タイトルバーを表示するかどうか */
    public $isShowedTitleBar = true;

    /** @var bool フッタメニューを表示するかどうか */
    public $isShowedFooterMenu = false;

    /** @var bool ヘッダにお知らせへのリンクを表示するかどうか */
    public $isShowedInformationLink = false;

    /** @var string|null タイトルバーに表示する文字列(nullの場合はページタイトルと同じ) */
    public $specifiedTitle = null;

    /**
     * 戻るボタン押下時のリンク先
     * - 指定方法は `Url::to()` と同じ
     * - `null`の場合はデフォルトの戻り先
     * - `false`の場合は戻るボタン非表示
     * @var array|string|bool|null
     */
    public $backAction = null;

    /**
     * コンテンツHTMLタグの追加クラス
     * @var null|string
     */
    public $contentsHtmlClass = null;

    /**
     * 戻るボタンでの戻り先アクションを返す
     * @return array|bool|string|null インデックページのアクション(URL::to()で使用する)
     */
    public function getBackAction()
    {
        if ($this->backAction === false) {
            return null;
        } else if (is_null($this->backAction)) {
            return Dispatcher::forDefaultBackAction($this->user);
        } else {
            return $this->backAction;
        }
    }

    /**
     * 本番モードかどうかを返す
     * @return bool
     */
    public function isReleaseMode()
    {
        return !$this->isDevelopMode() && !$this->isDemoMode() && !$this->isTestMode();
    }

    /**
     * 開発モードかどうかを返す
     * @return bool
     */
    public function isDevelopMode()
    {
        $env = Environment::get();
        return ($env['mode'] === 'dev');
    }

    /**
     * デモモードかどうかを返す
     * @return bool
     */
    public function isDemoMode()
    {
        $env = Environment::get();
        return ($env['mode'] === 'demo');
    }

    /**
     * テストモードかどうかを返す
     * @return bool
     */
    public function isTestMode()
    {
        $env = Environment::get();
        return ($env['mode'] === 'test');
    }
}