<?php
/* @var $this \app\views\View */

use yii\helpers\Html;
use yii\helpers\Url;
use app\Environment;

$env = Environment::get();
$url = $env['cedynaMyPageUrls']['login'];
?>
<div class="charge_means_fancybox" style="width:300px;">
    <p class="point_site_name">クレジットカード</p>
    <p class="point_site_rogo"><?= $this->img("credit-card") ?></p>
    <div class="property_point_box clearfix">
        <p class="property_point_text">チャージレート</p>
        <p class="property_point_price">
            <span>1</span>円
            <?= $this->img('ico_arrw_red', ['class' => 'ico_arrw_red']) ?>
            <span>1</span>円 + <span>0.5</span>％
        </p>
    </div>
    <div class="fancybox_site_text_box">
        <p class="credit_card_charge_caution ">クレジットカードでのチャージは、<span class="bold">「会員専用サイト」</span>にて行うことができます。</p>
        <p class="credit_card_charge_caution mt5">また、クレジットカードでのチャージ増量分0.5％の<span class="bold red">反映は、翌月の末日</span>となります。</p>
        <p class="credit_card_charge_caution mt5">
            なお、クレジットカードでのチャージ手数料は、<br/>
            ◯セディナカード・・・<span class="bold">無料</span><br/>
            ◯その他カード・・・<span class="bold">300円/回</span><br/>
            となっております。予めご了承ください。
        </p>
    </div>
    <p class="btn_red btn_login">
        <?= Html::a('会員専用サイトへ行く', Url::to($url), ['target' => '_blank'])?>
    </p>
</div>
