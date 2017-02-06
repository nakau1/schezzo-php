<?php

namespace app\modules\exchange;

use app\modules\ReconfigureTrait;

class Module extends \yii\base\Module
{
    use ReconfigureTrait;

    public $controllerNamespace = 'app\modules\exchange\controllers';

    public function init()
    {
        parent::init();

        $this->reconfigure(require __DIR__ . '/config/exchange.php');
    }
}
