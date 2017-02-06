<?php
/* @var $this app\views\View */
/* @var $loginFail bool */
/* @var $tradingHistories app\models\TradingHistory[] */

use app\helpers\Format;
use yii\helpers\Html;
?>
<?php if ($loginFail): ?>
    Unauthorized
<?php elseif (!$tradingHistories): ?>
    <div class="main_box">
        <div class="charg_price_comf_text clearfix">ご利用はありません。</div>
    </div>
<?php else: ?>
    <table cellpadding="0" cellspacing="0" class="details_table">
        <?php foreach ($tradingHistories as $tradingHistory): ?>
            <tr>
                <td class="day_td"><?= $tradingHistory->tradingDate->format('j') ?>日</td>
                <td class="shop_td"><?= Html::encode($tradingHistory->shop) ?></td>
                <td class="price_td"><?= Html::encode($tradingHistory->tradingType) ?><br>
                    <span><?= Format::formattedNumber($tradingHistory->spentValue) ?></span>円</td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>
