<?php
use bryglen\apnsgcm\Apns;

$envConf = \app\Environment::get();

return [
    'id' => 'pollet',
    'language' => 'ja',
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'runtimePath' => dirname(dirname(__DIR__)) . '/runtime',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'components' => [
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
            'useFileTransport' => true,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                    'dirMode' => 0777,
                    'fileMode' => 0666,
                ],
            ],
        ],
        'fileCache' => [
            'class' => 'yii\caching\FileCache',
            'cachePath' => __DIR__ . '/../../runtime/cache',
        ],
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=' . $envConf['db']['host'] . ';dbname=' . $envConf['db']['database'],
            'username' => $envConf['db']['username'],
            'password' => $envConf['db']['password'],
            'enableSchemaCache' => YII_DEBUG ? false : true,
            'schemaCache' => 'fileCache',
            'charset' => 'utf8',

        ],
        'cache'                => [
            'useMemcached' => true,
            'class'        => 'yii\caching\MemCache',
            'servers'      => [
                [
                    'host'   => $envConf['memcacheHost'],
                    'port'   => 11211,
                    'weight' => 40,
                ],
            ],
        ],
        'session'              => [
            'class' => 'yii\web\CacheSession',
        ],
        'cedynaMyPage' => [
            'class' => 'app\components\CedynaMyPage',
            'urls' => $envConf['cedynaMyPageUrls'],
        ],
        'cedynaMyPageWithCache' => [
            'class' => 'app\components\CedynaMyPageWithCache',
            'urls' => $envConf['cedynaMyPageUrls'],
            'cardValueCacheSeconds' => 60 * 10,
            'tradingHistoryCacheSeconds' => 60 * 10,
        ],
        'curl' => [
            'class' => '\linslin\yii2\curl\Curl'
        ],
        'apns'      => [
            'class'       => 'bryglen\apnsgcm\Apns',
            'environment' => Apns::ENVIRONMENT_PRODUCTION,
            'pemFile'     => dirname(__FILE__) . '/../files/pems/demo/production_com.polletcorp.polletdemo.pem',
            // 'retryTimes' => 3,
            'options'     => [
                'sendRetryTimes' => 5,
            ],
        ],
        'gcm'       => [
            'class'  => 'bryglen\apnsgcm\Gcm',
            'apiKey' => YII_ENV != 'production' ? 'AIzaSyC6IludhrSjP7MWRVPN9QKfhvU7l-zG9fA' : 'AIzaSyCdtONYKSChbpUtz61YmU4A6S-3mesI_w8',
        ],
        // using both gcm and apns, make sure you have 'gcm' and 'apns' in your component
        'apnsGcm'   => [
            'class' => 'bryglen\apnsgcm\ApnsGcm',
            // custom name for the component, by default we will use 'gcm' and 'apns'
            //'gcm' => 'gcm',
            //'apns' => 'apns',
        ],
        'pushNotify'      => [
            'class'       => 'app\components\PushNotify',
        ],
        'slack'      => [
            'httpclient' => [
                'class' => 'yii\httpclient\Client',
            ],
            'class'      => 'understeam\slack\Client',
            'url'        => 'https://hooks.slack.com/services/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',
            'username'   => 'pollet-report',
        ],
    ],
    'params' => [
        'appHost' => $envConf['appHost'],
        'supportTo' => $envConf['supportTo'],
        'batchTo' => $envConf['batchTo'],
    ],
];
