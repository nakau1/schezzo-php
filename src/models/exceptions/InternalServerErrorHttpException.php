<?php

namespace app\models\exceptions;

use yii\web\HttpException;

/**
 * Class InternalServerErrorHttpException
 * @package app\models\exceptions
 */
class InternalServerErrorHttpException extends HttpException
{
    public function __construct($message = null, $code = 0, \Exception $previous = null)
    {
        if (!$message) {
            $message = "サーバーエラーが発生しました。\nもう一度操作をやり直してください。";
        }
        parent::__construct(400, $message, $code, $previous);
    }
}
