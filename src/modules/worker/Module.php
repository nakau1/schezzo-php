<?php

namespace app\modules\worker;

use app\modules\ReconfigureTrait;
use Yii;

class Module extends \yii\base\Module
{
    use ReconfigureTrait;

    public $controllerNamespace = 'app\modules\worker\controllers';

    public function init()
    {
        parent::init();

        $this->reconfigure(require __DIR__ . '/config/worker.php');
    }
}
