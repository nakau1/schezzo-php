<?php

namespace app\models\exceptions;

/**
 * Class UnauthorizedHttpException
 * @package app\models\exceptions
 */
class UnauthorizedHttpException extends \yii\web\UnauthorizedHttpException
{
    public function __construct()
    {
        parent::__construct('このサイトは閲覧できません');
    }
}
