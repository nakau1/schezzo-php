<?php
namespace app\assets;

use yii\web\AssetBundle;
use yii\web\View;

/**
 * 基本アセット
 * Class AppAsset
 * @package app\assets
 */
class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl  = '@web';

    public $css = [
        'css/default.css',
        'css/common.css',
    ];

    public $js = [
        'js/jquery.min.js',
    ];

    public $jsOptions = ['position' => View::POS_HEAD];
}