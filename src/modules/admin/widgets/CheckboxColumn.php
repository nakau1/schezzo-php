<?php
namespace app\modules\admin\widgets;

use yii\grid\DataColumn;
use yii\helpers\Html;

/**
 * Class LinkColumn
 * @package app\modules\admin\widgets
 */
class CheckboxColumn extends DataColumn
{
    public $format = 'raw';

    public $enableSorting = false;

    public $contentOptions = [
        'class' => 'text-center',
    ];

    public function init()
    {
        parent::init();
        $this->value = function ($model) {
            $property = $this->attribute;
            return Html::checkbox('', $model->$property, [
                'class'   => $property. '_check',
                'data-id' => $model->id,
            ]);
        };
    }
}