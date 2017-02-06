<?php
namespace app\models\exceptions;

/**
 * Internal Server Error
 * @package app\models\exceptions
 */
class InternalServerErrorHttpException extends HttpException
{
    protected function fixedMessage() : string
    {
        return 'サーバエラーが発生しました';
    }

    protected function fixedStatusCode() : int
    {
        return 500;
    }
}
