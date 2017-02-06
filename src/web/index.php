<?php
require_once __DIR__ . '/../Environment.php';

$envConf = \app\Environment::get();

defined('YII_DEBUG') or define('YII_DEBUG', isset($envConf['mode']) ? in_array($envConf['mode'], ['dev', 'demo', 'test']) : true);
defined('YII_ENV') or define('YII_ENV', isset($envConf['mode']) ? $envConf['mode'] : 'dev');

require(__DIR__ . '/../../vendor/autoload.php');
require(__DIR__ . '/../../vendor/yiisoft/yii2/Yii.php');

$config = require(__DIR__ . '/../config/web.php');

(new yii\web\Application($config))->run();
