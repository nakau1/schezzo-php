<?php

namespace app\modules\admin\controllers;

use Yii;
use yii\web\Controller;

/**
 * Class DefaultController
 * @package app\modules\api\controllers
 */
class DefaultController extends Controller
{
    public function actionIndex()
    {
        var_dump($_SERVER);
    }
}
