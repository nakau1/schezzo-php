<?php
/* @var $this \app\views\View */
/* @var $percentage float */
/* @var $chargeSources ChargeSource[] */

use app\assets\TopAsset;
use app\helpers\Format;
use app\controllers\ChargeController;
use app\models\ChargeSource;
use yii\helpers\Html;
use yii\helpers\Url;

$this->contentsHtmlClass = ($this->user->isActivatedUser()) ?
    'after_activating' :
    'before_activating';

$this->isShowedTitleBar = false;
$this->isShowedFooterMenu = true;
$this->isGrayBackground = false;
$this->isShowedInformationLink = true;
$this->title = 'Pollet';

TopAsset::register($this);
?>
    <!--ドーナツチャート-->
    <div class="card_box clearfix">
    <div class="card_box_inner">
        <canvas class="graph" id="canvas" height="180" width="180"></canvas>
        <?php if ($this->user->isActivatedUser()): ?>
        <div class="balance_box">
            <?php else: ?>
            <div class="balance_box_center">
                <?php endif; ?>
                <div class="balance_loading_image center">
                    <?= $this->img('loading01', ['extension' => 'gif', 'width' => '48px', 'height' => '48px']) ?>
                </div>
                <div class="balance_text" style="display: none;">
                    <p>カード残高</p>
                    <p class="balance_price"><span></span>円</p>
                    <?php if ($this->user->isActivatedUser()): ?>
                        <p class="btn_reload"><?= Html::a('表示の更新', Url::to('/top')) ?></p>
                    <?php endif; ?>
                </div>
            </div>
            <?php if ($this->user->isChargeRequested() || $this->user->isWaitingIssue()): ?>
                <div class="card_btn_box make_box">
                    <?= Html::a('', ['issuance/']) ?>
                    <p class="btn_red btn_before">
                        <?= Html::a('カードを作る', ['#']) ?>
                    </p>
                </div>
            <?php elseif ($this->user->isIssued()): ?>
                <div class="card_btn_box start_box">
                    <?= Html::a('', ['auth/sign-in']) ?>
                    <p class="btn_red btn_before">
                        <?= Html::a('使いはじめる', ['#']) ?>
                    </p>
                </div>
                <div class="start_box_text center">
                    カードがお手元に届き次第、<br>
                    こちらよりお進みください。<br>
                    郵送はお申込から最大2週間です。
                </div>
            <?php elseif ($this->user->isActivatedUser()): ?>
                <div class="card_btn_box">
                    <p class="btn_red btn_charge">
                        <?= Html::a('チャージする', ['charge/list']) ?>
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <!--チャージサイト追加-->
    <div class="site_box clearfix">
        <?php if ($this->user->isIssued()): ?>
            <p class="btn_until_card"><?= Html::a('<span>カードが届くまで</span>', ['guide/first/flow', 'back' => 'top/']) ?></p>
        <?php endif; ?>
        <?php if ($this->user->isActivatedUser()): ?>
            <div class="slider multiple-items site_add">
                <?php foreach ($chargeSources as $chargeSource):
                    $myValidPoint = $chargeSource->myValidPoint;
                    if ($myValidPoint === false) {
                        continue;
                    }
                    ?>
                    <div class="site_add_inner">
                        <div class="site_rogo_img">
                            <a href="<?= Url::to([
                                'charge/price',
                                'code' => $chargeSource->charge_source_code,
                                'mode' => ChargeController::PRICE_MODE_NORMAL,
                            ]) ?>">
                                <?= Html::img($chargeSource->icon_image_url) ?>
                            </a>
                        </div>
                        <p class="site_add_text">ポイント残高</p>
                        <p class="site_add_price">
                            <span><?= Format::formattedNumber($myValidPoint) ?></span>円分</p>
                    </div>
                <?php endforeach; ?>
                <div class="site_add_inner">
                    <div class="site_rogo_img"><a
                            href="<?= Url::to(['charge/list']) ?>"><?= $this->img('img_add_charge_site') ?></a></div>
                </div>
            </div>
        <?php endif; ?>
    </div>
<?= Html::hiddenInput('userToken', $this->user->user_code_secret, [
    'id' => 'user-token',
]) ?>
<?= Html::hiddenInput('activated', $this->user->isActivatedUser()) ?>