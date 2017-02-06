<?php
/* @var $this \app\views\View */

use yii\helpers\Html;
use app\Environment;

$env = Environment::get();
$url = $env['cedynaMyPageUrls']['passwordReset'];

$this->backAction = ['guide/'];
$this->isGrayBackground = false;
$this->title = 'ログインパスワード';
?>
<div class="main_box card_details_box">
    <h2>ログインパスワード</h2>
    <div class="text_card_details_box">
        <p>ログインパスワードとは、Polletカードのお申し込み時にご自身で登録した8~12ケタの半角英数字です。<br>
            PolletアプリやPollet会員専用サイトにログインする際に必要になります。</p>
        <p class="text_ss right">（ログインパスワードのお忘れ/変更は<?= Html::a('Pollet会員専用サイト', $url, ['target' => '_blank']) ?>から）</p>
    </div>
</div>