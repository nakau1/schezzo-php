<?php
namespace app\controllers;

use app\views\View;
use Yii;

/**
 * WEBコントローラの基底抽象クラス
 * Class Controller
 * @package app\controllers
 */
abstract class Controller extends \yii\web\Controller
{
    /**
     * @inheritdoc
     */
    public function render($view, $params = [])
    {
        /** @var $viewObject View */
        $viewObject = $this->view;
        $viewObject->user = Yii::$app->user->identity;
        return parent::render($view, $params);
    }
}