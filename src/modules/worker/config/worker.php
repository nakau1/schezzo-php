<?php
return [
    'id'                  => 'pollet-api',
    'controllerNamespace' => 'app\modules\worker\controllers',
    'components'          => [
        'errorHandler' => [
            'class'       => 'yii\web\ErrorHandler',
            'errorAction' => 'worker/default/error',
        ],
        'response'     => [
            'class'       => 'yii\web\Response',
            'on beforeSend' => function (\yii\base\Event $event) {
                /** @var \yii\web\Response $response */
                $response = $event->sender;

                // エラー時のレスポンスを設定する
                if ($response->data !== null && !$response->isSuccessful) {
                    $response->data = [
                        'code'    => $response->getStatusCode(),
                        'message' => isset($response->data['message']) ? $response->data['message'] : '',
                    ];
                }
            },
        ],
    ],
];