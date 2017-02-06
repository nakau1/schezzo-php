<?php
/* @var $this \app\views\View */
/* @var $chargeSource \app\models\ChargeSource */
/* @var $state string */

use app\controllers\ChargeController;
use app\helpers\Format;
use yii\helpers\Html;
use yii\helpers\Url;
?>
<div class="charge_means_fancybox" style="width:300px;">
    <p class="point_site_name"><?= Html::encode($chargeSource->site_name) ?></p>
    <p class="point_site_rogo"><?= Html::img($chargeSource->icon_image_url) ?></p>
    <div class="property_point_box clearfix">
        <p class="property_point_text">チャージレート</p>
        <p class="property_point_price">
            <span><?= Format::formattedNumber($chargeSource->introduce_charge_rate_point) ?></span><?= Html::encode($chargeSource->denomination) ?>
            <?= $this->img('ico_arrw_red', ['class' => 'ico_arrw_red']) ?>
            <span><?= Format::formattedNumber($chargeSource->introduce_charge_rate_price) ?></span>円
        </p>
    </div>
    <div class="fancybox_site_text_box">
        <p><?= Html::encode($chargeSource->description) ?></p>
    </div>
    <p class="btn_red btn_login">
        <?php if ($chargeSource->requiresAuthorization()) : ?>
        <?= Html::a(
            'サイトでログイン',
            $chargeSource->auth_url. '?state=' . rawurlencode($state),
            ['target' => '_blank']
        ) ?>
        <?php else : ?>
        <?= Html::a('チャージ金額選択', [
            'charge/price',
            'code' => $chargeSource->charge_source_code,
            'mode' => $this->user->isActivatedUser() ? ChargeController::PRICE_MODE_NORMAL : ChargeController::PRICE_MODE_FIRST
        ]) ?>
        <?php endif; ?>
    </p>
</div>
