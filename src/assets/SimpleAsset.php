<?php
namespace app\assets;

use yii\web\AssetBundle;

/**
 * 'simple'レイアウト用のアセットクラス
 * @package app\assets
 */
class SimpleAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl  = '@web';

    public $css = [
        'css/site.css',
    ];

    public $js = [
    ];

    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];
}