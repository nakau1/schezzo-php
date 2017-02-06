<?php
namespace app\models\exceptions;

/**
 * HTTP例外の基底抽象クラス
 * Class HttpException
 * @package app\models\exceptions
 */
abstract class HttpException extends \yii\web\HttpException
{
    /**
     * @return string
     */
    abstract protected function fixedMessage() : string;

    /**
     * @return int
     */
    abstract protected function fixedStatusCode() : int;

    /**
     * HttpException constructor.
     */
    public function __construct()
    {
        parent::__construct($this->fixedStatusCode(), $this->fixedMessage(), 0, null);
    }
}
