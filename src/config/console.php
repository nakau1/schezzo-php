<?php
Yii::setAlias('@tests', dirname(__DIR__) . '/tests');

$config = array_merge_recursive(require(__DIR__ . '/common.php'), [
    'id' => 'pollet-console',
    'controllerNamespace' => 'app\commands',
    'components' => [
        'hulft' => [
            'class'        => $envConf['mode'] === 'prod'
                ? 'app\components\Hulft'
                : 'app\components\HulftDummy',
            'host'         => $envConf['hulft']['host'] ?? '',
            'user'         => $envConf['hulft']['user'] ?? '',
            'identityFile' => $envConf['hulft']['identityFile'] ?? '',
        ],
    ],
    'params' => [
        'hulftPath' => $envConf['mode'] === 'dev'
            ? dirname(dirname(__DIR__)) . '/runtime/hulft'
            : '/var/hulft',
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
