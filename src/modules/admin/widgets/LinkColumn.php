<?php
namespace app\modules\admin\widgets;

use yii\bootstrap\Html;
use yii\grid\DataColumn;

/**
 * Class LinkColumn
 * @package app\modules\admin\widgets
 */
class LinkColumn extends DataColumn
{
    public $format = 'raw';

    public $enableSorting = false;

    public $url = '';

    public function init()
    {
        parent::init();
        $this->value = function ($model) {
            $property = $this->attribute;
            if (is_callable($this->url)) {
                $url = call_user_func($this->url, $model);
            } else {
                $url = $this->url;
            }
            return Html::a($model->$property, $url);
        };
    }
}