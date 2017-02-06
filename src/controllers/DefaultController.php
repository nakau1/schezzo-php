<?php
namespace app\controllers;

/**
 * Class DefaultController
 * @package app\controllers
 */
class DefaultController extends Controller
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

    /**
     * エラー画面
     * @return string
     */
    public function actionError()
    {
        return $this->render('error', [
            // empty
        ]);
    }
}
