<?php
/* @var $this \app\views\View */

use yii\helpers\Html;
use app\Environment;

$env = Environment::get();
$url = $env['cedynaMyPageUrls']['memberSiteLink'];

$this->backAction = ['guide/'];
$this->isGrayBackground = false;
$this->title = 'カード暗証番号';
?>
<div class="main_box card_details_box">
    <h2>カード暗証番号</h2>
    <div class="text_card_details_box">
        <p>カード暗証番号とは、Pollet Visa Prepaidカードのお申し込み時にご自身で登録した4ケタの半角数字です。一部の券売機や自動精算機で入力が必要な場合があります。</p>
        <p class="text_ss right">（ログインパスワードのお忘れ/変更は<?= Html::a('Pollet会員専用サイト', $url, ['target' => '_blank']) ?>から）</p>
    </div>
</div>