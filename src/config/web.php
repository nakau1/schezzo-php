<?php
use app\components\Slack;

$config = array_merge_recursive(require(__DIR__ . '/common.php'), [
    'id'                  => 'pollet-web',
    'controllerNamespace' => 'app\controllers',
    'components'          => [
        'request'      => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'owq0RvztGJFhFz9-uoO3buZk1j_AhW8Y',
        ],
        'urlManager'   => [
            'enablePrettyUrl' => true,
            'showScriptName'  => false,
            'rules'           => require(__DIR__ . '/routes.php'),
        ],
        'user'         => [
            'class'           => 'app\components\User',
            'identityClass'   => 'app\models\PolletUser',
            'loginUrl'        => ['/'],
            'enableAutoLogin' => true,
        ],
        'errorHandler' => [
            'errorAction' => 'default/error',
        ],
        'view'         => [
            'class' => '\app\views\View',
        ],
        'assetManager' => [
            'appendTimestamp' => true,
            'hashCallback'    => function ($path) {
                return hash('md4', $path);
            },
        ],
        'response'     => [
            'class'         => 'yii\web\Response',
            'on beforeSend' => function (\yii\base\Event $event) {
                /** @var \yii\web\Response $response */
                $response = $event->sender;

                // エラー時のレスポンスを設定する
                if ($response->data !== null && !$response->isSuccessful) {
                    Slack::send(
                        'Error',
                        $response->statusText,
                        implode("\n", [
                            (isset($response->data['message']) ? $response->data['message'] : ''),
                            'Stack Trace',
                            Yii::$app->errorHandler->exception->getTraceAsString(),
                        ])
                    );
                }
            },
        ],
    ],
    'modules'             => [
        // API
        'api'      => [
            'class' => 'app\modules\api\Module',
        ],
        // admin
        'admin'    => [
            'class' => 'app\modules\admin\Module',
        ],
        // worker
        'worker'   => [
            'class' => 'app\modules\worker\Module',
        ],
        // 交換API
        'exchange' => [
            'class' => 'app\modules\exchange\Module',
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
