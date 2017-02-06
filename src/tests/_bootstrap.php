<?php
// This is global bootstrap for autoloading
require_once __DIR__ . '/../Environment.php';

$envConf = \app\Environment::get();

defined('YII_DEBUG') or define('YII_DEBUG', isset($envConf['mode']) ? in_array($envConf['mode'], ['dev']) : true);
defined('YII_ENV') or define('YII_ENV', isset($envConf['mode']) ? $envConf['mode'] : 'dev');

if (YII_ENV !== 'dev') {
    throw new Exception('この環境での実行は許可されていません');
}

require(__DIR__ . '/../../vendor/autoload.php');
require(__DIR__ . '/../../vendor/yiisoft/yii2/Yii.php');

$config = require(__DIR__ . '/../config/console.php');
$application = new yii\console\Application($config);
