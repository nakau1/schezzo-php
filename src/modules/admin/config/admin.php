<?php
return [
    'id'                  => 'pollet-admin',
    'controllerNamespace' => 'app\modules\admin\controllers',
    'components'          => [
        'errorHandler' => [
            'class'       => 'yii\web\ErrorHandler',
            'errorAction' => 'admin/default/error',
        ],
        'view'         => [
            'class' => '\app\modules\admin\views\View',
        ],
    ],
];