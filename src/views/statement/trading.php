<?php
/* @var $this \app\views\View */
/* @var $currentYear integer */
/* @var $currentMonth integer */
/* @var $nextMonthString string */
/* @var $prevMonthString string */
/* @var $argument string */

use yii\web\JqueryAsset;
use yii\helpers\Html;

$this->registerJsFile('/js/app/statement-trading.js', [
    'depends' => [
        JqueryAsset::className(),
    ],
]);

$this->isGrayBackground = false;
$this->title = '利用明細';
?>

<div class="main_box">
    <div class="details_head clearfix">
        <p class="details_head_arrw">
            <?=
            $this->anchorImg('ico_arrw_gray01', ['trading', 'month' => $prevMonthString], [], [
                'style' => ['visibility' => (!$prevMonthString) ? 'hidden' : 'block'],
            ])
            ?>
        </p>
        <p class="details_head_text"><?= $currentYear ?>年<?= sprintf('%02d', $currentMonth) ?>月</p>
        <p class="details_head_arrw">
            <?=
            $this->anchorImg('ico_arrw_gray02', ['trading', 'month' => $nextMonthString], [], [
                'style' => ['visibility' => (!$nextMonthString) ? 'hidden' : 'block'],
            ])
            ?>
        </p>
    </div>
</div>
<?= Html::hiddenInput('month', $argument) ?>
<?= Html::hiddenInput('userToken', $this->user->user_code_secret, [
    'id' => 'user-token',
]) ?>

<div id="trading_list">
    <div class="main_box">
        <div class="charg_price_comf_text clearfix"><?= $this->img('loading01', ['extension' => 'gif', 'width' => '48px', 'height' => '48px']) ?></div>
    </div>
</div>
