<?php
$config = array_merge_recursive(require(__DIR__ . '/common.php'), [
    'id'                  => 'schezzo-web',
    'controllerNamespace' => 'app\controllers',

    // components
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'schezzoAppFhFz9-uoO3buZk1j_AhW8Y',
        ],
        'urlManager'   => [
            'enablePrettyUrl' => true,
            'showScriptName'  => false,
            'rules'           => require(__DIR__ . '/routes.php'),
        ],
        'user'         => [
            'class'           => 'yii\web\User',
            'identityClass'   => 'app\models\User',
            'loginUrl'        => ['/auth'],
            'enableAutoLogin' => true,
        ],
        'errorHandler' => [
            'errorAction' => 'default/error',
        ],
        'view' => [
            'class' => '\app\views\View',
        ],
        'assetManager' => [
            'appendTimestamp' => true,
            'hashCallback'    => function ($path) {
                return hash('md4', $path);
            },
        ],
        'response' => [
            'class' => 'yii\web\Response',
        ],
    ],
    'modules' => [
        // API
        'api' => [
            'class' => 'app\modules\api\Module',
        ],
        // admin
        'admin' => [
            'class' => 'app\modules\admin\Module',
        ],
    ],
]);

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class'      => 'yii\debug\Module',
        'allowedIPs' => ['*'],
    ];
    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class'      => 'yii\gii\Module',
        'allowedIPs' => ['*'],
    ];
}

return $config;
