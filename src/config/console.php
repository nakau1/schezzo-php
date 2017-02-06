<?php
$config = array_merge_recursive(require(__DIR__ . '/common.php'), [
    'id' => 'schezzo-console',
    'controllerNamespace' => 'app\commands',

    // components
    'components' => [
        // enpty
    ],

    // params
    'params' => [
        // empty
    ],
]);

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
    ];
}

return $config;
