<?php
use app\components\Slack;

return [
    'id'                  => 'pollet-api',
    'controllerNamespace' => 'app\modules\api\controllers',
    'components'          => [
        'errorHandler' => [
            'class'       => 'yii\web\ErrorHandler',
            'errorAction' => 'api/default/error',
        ],
        'response'     => [
            'class'       => 'yii\web\Response',
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

                    $response->data = [
                        'code'    => $response->getStatusCode(),
                        'message' => isset($response->data['message']) ? $response->data['message'] : '',
                    ];
                }
            },
        ],
    ],
];