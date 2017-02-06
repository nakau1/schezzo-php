<?php
namespace app\controllers;

/**
 * Class DemoController
 * @package app\controllers
 */
class DemoController extends Controller
{
    /**
     * インデックス画面
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index', [
            // empty
        ]);
    }
}
