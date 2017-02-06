<?php
namespace app\controllers\actions;

use Yii;
use yii\web\NotFoundHttpException;

/**
 * Class ErrorAction
 * @package app\controllers\actions
 */
class ErrorAction extends \yii\web\ErrorAction
{
    /**
     * @inheritdoc
     */
    public function run()
    {
        if (Yii::$app->getErrorHandler()->exception instanceof NotFoundHttpException) {
            Yii::$app->getErrorHandler()->exception = new NotFoundHttpException("ページが見つかりません(404)\nTOPページからやりなおしてください。");
        }

        $this->controller->layout = false;
        return parent::run();
    }
}