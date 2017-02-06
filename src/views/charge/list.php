<?php
/* @var $this \app\views\View */
/* @var $chargeSources \app\models\ChargeSource[] */

use app\controllers\ChargeController;
use app\assets\AppJQueryAsset;
use app\helpers\Format;
use yii\helpers\Html;
use yii\helpers\Url;

AppJQueryAsset::register($this);
$this->registerJsFile('/js/app/charge-list.js', [
    'depends' => [
        AppJQueryAsset::className(),
    ],
]);
$this->registerCssFile('/css/app/charge-list.css');

$this->title = 'どこからチャージしますか';
?>
<?php if (!$chargeSources): ?>
    <p class="alert alert-warning">サイトがありません</p>
<?php else: ?>
    <?php foreach ($chargeSources as $chargeSource) : ?>
    <!--item-->
    <div class="charge_means_box clearfix">
        <?php if ($chargeSource->isAuthorized): ?>
        <a class="clearfix" href="<?= Url::to([
            'charge/price',
            'code' => $chargeSource->charge_source_code,
            'mode' => $this->user->isActivatedUser() ? ChargeController::PRICE_MODE_NORMAL : ChargeController::PRICE_MODE_FIRST
        ]) ?>">
        <?php else: ?>
        <a class="clearfix fancybox" href="<?= Url::to(['charge/detail', 'id' => $chargeSource->id]) ?>">
        <?php endif; ?>
            <p class="charge_means_rogo"><?= Html::img($chargeSource->icon_image_url) ?></p>
            <div class="charge_means_text_box">
                <p class="charge_means_sitename"><?= Html::encode($chargeSource->site_name); ?></p>
                <p class="charge_means_point_text">
                    <span><?= Format::formattedNumber($chargeSource->introduce_charge_rate_point) ?></span><?= Html::encode($chargeSource->denomination); ?>
                    <?= $this->img('img_arrw') ?>
                    <span><?= Format::formattedNumber($chargeSource->introduce_charge_rate_price) ?></span>円</p>
            </div>
        </a>
    </div>
    <?php endforeach; ?>
    <?php if ($this->user->isActivatedUser()): ?>
    <!--item-->
    <div class="charge_means_box clearfix">
        <a class="clearfix fancybox" href="<?= Url::to(['charge/credit-card-detail']) ?>">
            <p class="charge_means_rogo"><?= $this->img("credit-card") ?></p>
            <div class="charge_means_text_box">
                <p class="charge_means_sitename">クレジットカード</p>
                <p class="charge_means_point_text">
                    <span>1</span>円
                    <?= $this->img('img_arrw') ?>
                    <span>1</span>円 + <span>0.5</span>％
                </p>
                <p class="credit_card_charge_caution mt10">※チャージ増量分0.5％の<span class="red">反映は、翌月の末日</span></p>
            </div>
        </a>
    </div>
    <?php endif; ?>
<?php endif; ?>
<?= $this->isReleaseMode() ? '' : Html::a('ポイントサイト連携解除（デモ用）', '/demo/remove-point-site-tokens'); ?>
