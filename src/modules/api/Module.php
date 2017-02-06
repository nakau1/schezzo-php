<?php

namespace app\modules\api;

use app\modules\ReconfigureTrait;
use Yii;

class Module extends \yii\base\Module
{
    use ReconfigureTrait;

    public $controllerNamespace = 'app\modules\api\controllers';

    public function init()
    {
        parent::init();

        $this->reconfigure(require __DIR__ . '/config/api.php');
    }
}
