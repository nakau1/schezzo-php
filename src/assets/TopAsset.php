<?php
namespace app\assets;

use yii\web\AssetBundle;
use yii\web\View;

/**
 * トップ画面用のアセットクラス
 * @package app\assets
 */
class TopAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl  = '@web';

    public $css = [
        'css/slick.css',
    ];

    public $js = [
        'js/DonutsCanvas.js',
        'js/slick.min.js',
        'js/imgLiquid-min.js',
        'js/app/top.js',
    ];

    public $depends = [
        'app\assets\AppAsset',
    ];

    public $jsOptions = ['position' => View::POS_BEGIN];
}