<?php
/* @var $this \app\views\View */

use yii\helpers\Html;
use app\Environment;

$env = Environment::get();
$url = $env['cedynaMyPageUrls']['memberSiteLink'];

$this->backAction = ['guide/'];
$this->isGrayBackground = false;
$this->title = 'カードの紛失';
?>
<div class="main_box card_details_box">
    <h2>カードの紛失</h2>
    <div class="text_card_details_box">
        <p>カードの紛失・盗難・事故・第三者による不正利用の場合、直ちに<?= Html::a('Pollet会員専用サイト', $url, ['target' => '_blank']) ?>から、カードを使えないように「一時停止」機能を設定することができます。</p>
    </div>
</div>