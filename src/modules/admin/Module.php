<?php

namespace app\modules\admin;

use app\modules\ReconfigureTrait;
use Yii;

class Module extends \yii\base\Module
{
    use ReconfigureTrait;

    public $controllerNamespace = 'app\modules\admin\controllers';

    public function init()
    {
        parent::init();
        $this->layout = 'main';
        $this->reconfigure(require __DIR__ . '/config/admin.php');
    }
}
