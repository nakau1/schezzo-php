<?php
namespace app\commands;

use Yii;
use yii\base\Application;

/**
 * コマンド(コンソール)コントローラの基底抽象クラス
 * Class Controller
 * @package app\commands
 */
abstract class Controller extends \yii\console\Controller
{
    public function init()
    {
        Yii::$app->on(Application::EVENT_BEFORE_ACTION, function () {
            Yii::info('begin batch: '.$this->getRoute());
        });
        Yii::$app->on(Application::EVENT_AFTER_ACTION, function () {
            Yii::info('finish batch: '.$this->getRoute());
        });
    }
}
