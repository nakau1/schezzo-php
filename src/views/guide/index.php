<?php
/* @var $this \app\views\View */

use yii\helpers\Html;

$this->title = '利用ガイド';
?>
<div class="main_box">
    <div class="main_box">
        <div class="guide_box clearfix">
            <p class="guide_box_h">はじめてガイド</p>
            <p class="guide_box_link"><?= Html::a('Pollet Visa Prepaidとは', ['guide/first/visa-prepaid']) ?></p>
            <p class="guide_box_link"><?= Html::a('ご利用までの流れ', ['guide/first/flow']) ?></p>
            <p class="guide_box_link"><?= Html::a('カードの使い方', ['guide/first/usage']) ?></p>
        </div>
        <div class="guide_box clearfix">
            <p class="guide_box_h">詳細ガイド</p>
            <p class="guide_box_link"><?= Html::a('会員番号', ['guide/detail/member-number']) ?></p>
            <p class="guide_box_link"><?= Html::a('ログインパスワード', ['guide/detail/login-password']) ?></p>
            <p class="guide_box_link"><?= Html::a('カード暗証番号', ['guide/detail/card-pin']) ?></p>
            <p class="guide_box_link"><?= Html::a('チャージについて', ['guide/detail/about-charge']) ?></p>
            <p class="guide_box_link"><?= Html::a('使えるお店/使えないお店', ['guide/detail/available-shops']) ?></p>
            <p class="guide_box_link"><?= Html::a('海外利用時の手数料', ['guide/detail/fee-in-foreign']) ?></p>
            <p class="guide_box_link"><?= Html::a('利用明細について', ['guide/detail/about-steatment']) ?></p>
            <p class="guide_box_link"><?= Html::a('カードの紛失', ['guide/detail/card-lost']) ?></p>
            <p class="guide_box_link"><?= Html::a('登録情報の変更', ['guide/detail/change-registration']) ?></p>
            <p class="guide_box_link"><?= Html::a('カードの停止/再開/解約', ['guide/detail/card-management']) ?></p>
            <p class="guide_box_link"><?= Html::a('カードの有効期限', ['guide/detail/card-expiration']) ?></p>
            <p class="guide_box_link"><?= Html::a('本アプリの端末推奨環境', ['guide/detail/recommended-environment']) ?></p>
        </div>
        <div class="guide_box clearfix">
            <p class="guide_box_h">利用規約</p>
            <p class="guide_box_link"><?= Html::a('アプリ利用規約', ['terms/']) ?></p>
            <p class="guide_box_link"><?= Html::a('カード利用規約', ['card-terms/']) ?></p>
            <p class="guide_box_link"><?= Html::a('プライバシーポリシー', ['privacy-policy/']) ?></p>
        </div>
        <div class="guide_box clearfix">
            <p class="guide_box_h">お問い合わせ</p>
            <p class="guide_box_link"><?= Html::a('お問い合わせフォーム', ['inquiry/']) ?></p>
        </div>
    </div>
</div>