<?php
namespace app\widgets;

/**
 * エラー表示対応用のカスタムなActiveFieldクラス
 * @package app\widgets
 */
class ActiveField extends \yii\widgets\ActiveField
{
    public $errorOptions = [
        'tag'   => 'p',
        'class' => 'err_text'
    ];
}