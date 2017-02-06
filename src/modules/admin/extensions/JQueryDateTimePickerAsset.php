<?php
namespace app\modules\admin\extensions;

use yii\web\AssetBundle;

/**
 * Class JQueryDateTimePickerAsset
 * @package app\modules\admin\extensions
 */
class JQueryDateTimePickerAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl  = '@web';

    public $js = [
        'extension/jquery.datetimepicker/jquery.datetimepicker.js',
    ];

    public $css = [
        'extension/jquery.datetimepicker/jquery.datetimepicker.css',
    ];

    public $depends = [
        'yii\web\JqueryAsset',
    ];
}