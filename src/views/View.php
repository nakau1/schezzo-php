<?php
namespace app\views;

use app\Environment;
use app\models\User;

/**
 * Class View
 * @package app\views
 */
class View extends \yii\web\View
{
    const JS_VOID = 'javascript:void(0)';

    /** @var User アクセスしているユーザ */
    public $user;

    /**
     * 開発モードかどうかを返す
     * @return bool
     */
    public function isDevelopMode()
    {
        $env = Environment::get();
        return ($env['mode'] === 'dev');
    }
}