<?php
namespace app\assets;

use yii\web\AssetBundle;
use yii\web\View;

/**
 * Class StartPageAsset
 * @package app\assets
 */
class StartPageAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl  = '@web';

    public $css = [
        'css/slick.css',
    ];

    public $js = [
        'js/slick.min.js',
        'js/jquery.cookie.js',
        'js/modalConfirm.js',
        'js/app/start.js',
    ];

    public $depends = [
        'app\assets\AppAsset',
    ];

    public $jsOptions = ['position' => View::POS_HEAD];
}