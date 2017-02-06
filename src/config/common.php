<?php
$envConf = \app\Environment::get();

return [
    'id'          => 'schezzo',
    'language'    => 'ja',
    'vendorPath'  => dirname(dirname(__DIR__)) . '/vendor',
    'runtimePath' => dirname(dirname(__DIR__)) . '/runtime',
    'basePath'    => dirname(__DIR__),
    'bootstrap'   => ['log'],

    // components
    'components' => [
        'mailer' => [
            'class'            => 'yii\swiftmailer\Mailer',
            'useFileTransport' => true,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class'    => 'yii\log\FileTarget',
                    'levels'   => ['error', 'warning'],
                    'dirMode'  => 0777,
                    'fileMode' => 0666,
                ],
            ],
        ],
        'fileCache' => [
            'class'     => 'yii\caching\FileCache',
            'cachePath' => __DIR__ . '/../../runtime/cache',
        ],
        'db' => [
            'class'             => 'yii\db\Connection',
            'dsn'               => 'mysql:host=' . $envConf['db']['host'] . ';dbname=' . $envConf['db']['database'],
            'username'          => $envConf['db']['username'],
            'password'          => $envConf['db']['password'],
            'enableSchemaCache' => YII_DEBUG ? false : true,
            'schemaCache'       => 'fileCache',
            'charset'           => 'utf8',
        ],
        'cache'                => [
            'class' => 'yii\caching\FileCache',
        ],
        'session'              => [
            'class' => 'yii\web\CacheSession',
        ],
        'curl' => [
            'class' => '\linslin\yii2\curl\Curl'
        ],
    ],

    // params
    'params' => [
        'appHost'   => $envConf['appHost'],
        'supportTo' => $envConf['supportTo'],
        'batchTo'   => $envConf['batchTo'],
    ],
];
