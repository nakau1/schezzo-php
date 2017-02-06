<?php
use app\modules\exchange\helpers\Messages;

return [
    'id'                  => 'pollet-exchange',
    'controllerNamespace' => 'app\modules\exchange\controllers',
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

                if ($response->statusCode === Messages::HTTP_MAINTENANCE) {
                    $response->data = ['message' => Messages::ERR_MAINTENANCE];
                } else if ($response->data !== null && !$response->isSuccessful) {
                    if (isset($response->data['type'])) {
                        if ($response->statusCode == Messages::HTTP_NOT_FOUND) {
                            $response->data = ['message' => Messages::ERR_NOT_FOUND];
                        } else {
                            $response->setStatusCode(Messages::HTTP_SERVER_ERROR);
                            $response->data = ['message' => Messages::ERR_SERVER_ERROR];
                        }
                    }
                }
            },
        ],
    ],
];