<?php
/* @var $this \app\views\View */

use yii\helpers\Html;
use app\Environment;

$env = Environment::get();
$url = $env['cedynaMyPageUrls']['memberSiteLink'];

$this->backAction = ['guide/'];
$this->isGrayBackground = false;
$this->title = '登録情報の変更';
?>
<div class="main_box card_details_box">
    <h2>登録情報の変更</h2>
    <div class="text_card_details_box">
        <p>各種登録情報の変更は、<?= Html::a('Pollet会員専用サイト', $url, ['target' => '_blank']) ?>から設定ができます。</p>
    </div>
</div>