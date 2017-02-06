<?php

namespace app\modules;

use Yii;

/**
 * Class ReconfigureTrait
 * @package app\modules
 */
trait ReconfigureTrait
{
    /**
     * @param $config
     */
    public function reconfigure($config)
    {
        if (isset($config['app'])) {
            Yii::configure(Yii::$app, $config['app']);
        }
        if (isset($config['module'])) {
            Yii::configure($this, $config['module']);
        }
        if (isset($config['components'])) {
            Yii::$app->setComponents($config['components']);
        }
    }
}
