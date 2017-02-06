<?php
/* @var $this \app\views\View */

$this->backAction = ['guide/'];
$this->isGrayBackground = false;
$this->title = '会員番号';
?>
<div class="main_box card_details_box">
    <h2>会員番号</h2>
    <div class="img_card_details_box"><?= $this->img('img_card_details01', ['class' => 'card_details01']) ?><</div>
    <div class="text_card_details_box">
        <p>会員番号とは、カード裏面右下に書かれた16桁の数字です。<br>
            PolletアプリやPollet会員専用サイトにログインする際に必要になります。<br>
            <span class="text_ss">※会員番号の変更はできません。</span></p>
    </div>
</div>