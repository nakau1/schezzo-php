<?php
/* @var $this \app\views\View */
/* @var $backAction array */

$this->backAction = $backAction;
$this->isGrayBackground = false;
$this->title = 'ご利用までの流れ';
?>
<div class="card_flow01 clearfix mt30">
    <div class="flow_ico"><?= $this->img('ico_flow01') ?></div>
    <div class="flow_text">
        <p class="flow_titile">1. 受信したメール内のURLをクリック</p>
        <p class="btn_flow"><span>メール</span></p>
    </div>
</div>
<div class="card_flow02 clearfix">
    <div class="flow_ico"><?= $this->img('ico_flow02') ?></div>
    <div class="flow_text">
        <p class="flow_titile">2. お申し込み情報の入力</p>
        <p class="btn_flow"><span>pollet会員専用サイト</span></p>
    </div>
</div>
<div class="card_flow03 clearfix">
    <div class="flow_ico"><?= $this->img('ico_flow03') ?></div>
    <div class="flow_text">
        <p class="flow_titile">3. カード受取り</p>
        <p class="text_s"> ※最大２週間でお手元に。</p>
        <p class="btn_flow"><span>封筒</span></p>
    </div>
</div>
<div class="card_flow04 clearfix">
    <div class="flow_ico"><?= $this->img('ico_flow04') ?></div>
    <div class="flow_text">
        <p class="flow_titile">4. カード認証</p>
        <p class="text_s">※polletアプリを開いて「使いはじめる」ボタンから認証画面へ移動してください。</p>
        <p class="btn_flow"><span>polletアプリ</span></p>
    </div>
</div>
<div class="card_flow_end">
    <p class="flow_titile_end">ご利用準備完了</p>
    <p class="btn_flow_end"><span>polletアプリ</span></p>
    <?= $this->img('ico_flow05', ['class' => 'flow_ico_end']) ?>
</div>