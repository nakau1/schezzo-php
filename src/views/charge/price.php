<?php
/* @var $this \app\views\View */
/* @var $mode string */
/* @var $chargeSource \app\models\ChargeSource */
/* @var $formModel \app\models\forms\ChargePriceForm */
/* @var $chargeRemain integer */
/* @var $isFirst boolean */

use app\views\View;
use app\assets\AppJQueryAsset;
use app\helpers\Format;
use yii\helpers\Html;
use app\widgets\ActiveForm;
use app\components\GeneralChargeBonus;

AppJQueryAsset::register($this);
$this->registerJsFile('/js/app/charge-price.js', [
    'depends' => [
        AppJQueryAsset::className(),
    ],
]);

$this->title = "{$chargeSource->site_name}からチャージ";
?>
    <div class="main_box">
        <?php
        $form = ActiveForm::begin(['id' => 'charge-price']);
        ?>
        <div class="charge_card_box clearfix">
            <div class="charge_card_box_ico"><img src="<?= $chargeSource->icon_image_url ?>" width="60"></div>
            <div class="charge_card_box_float">
                <p class="charge_card_box_text">チャージ可能ポイント</p>
                <p class="charge_card_box_price"><span><?= Format::formattedNumber($chargeRemain) ?></span>円分</p>
            </div>
        </div>
        <p class="h_input">チャージ額</p>

        <?=
        $form->field($formModel, 'price', [
            'template' => '<div class="charge_price_box">' .$this->anchorImg('ico_closs_red', View::JS_VOID, ['class' => 'ico_closs'], ['id' => 'reset-price']) . '{input} 円</div>{error}',
            'options' => ['class' => null],
        ])->input('tel', [
            'class' => 'input_style_noborder',
        ])->label(false);
        ?>
        <div class="clearfix">
            <p class="btn_charge_price btn_red"><?= Html::a('100円',   View::JS_VOID, ['id' => 'add-100']) ?></p>
            <p class="btn_charge_price btn_red"><?= Html::a('1,000円', View::JS_VOID, ['id' => 'add-1000']) ?></p>
            <p class="btn_charge_price btn_red"><?= Html::a('5,000円', View::JS_VOID, ['id' => 'add-5000']) ?></p>
        </div>
        <p class="text_s mt15">チャージ額は直接入力することも可能です。なお、ハピタスからのチャージ額の上限は、月間30万円分までです。</p>

        <p class="btn_red btn_common mt50 mb10">
            <?= $this->anchorButton('確認', ['id' => 'confirm-charge-button']) ?>
        </p>
        <p class="btn_gray btn_common btn_disabled mt50 mb10"><span id="confirm-charge-disabled">確認</span></p>
        <?php ActiveForm::end(); ?>
    </div>
<?= Html::hiddenInput('card-issue-fee', $chargeSource->card_issue_fee) ?>
<?= Html::hiddenInput('min-price', $chargeSource->min_value) ?>
<?= Html::hiddenInput('charge-source-code', $chargeSource->charge_source_code) ?>
<a id="confirm-trigger" class="fancybox" href='#confirm'></a>

    <!--ライトボックス-->
    <div style="display: none;">
        <div id="confirm" style="width:300px;">
            <p class="img_charg_site_rogo center mt5"><img src="<?= $chargeSource->icon_image_url ?>"></p>
            <p class="img_charg_arrw center mt10"><?= $this->img('img_charg_arrw') ?></p>
            <p class="img_charg_card center mt10"><?= $this->img('img_charg_card') ?></p>
            <div class="charge_card_list_box">
                <div class="charge_card_list_box_inner">
                    <table>
                        <tr>
                            <th>チャージ額</th>
                            <td><span id="confirm-total-price">0</span>円</td>
                        </tr>
                        <tr>
                            <th>初回発行手数料</th>
                            <td><span id="confirm-card-issue-fee"><?= number_format(-$chargeSource->card_issue_fee) ?></span>円</td>
                        </tr>
                        <tr class="tr_increase">
                            <th>増量チャージ（<?= GeneralChargeBonus::getPercentage() ?>％）</th>
                            <td><span id="confirm-bonus-price">0</span>円</td>
                        </tr>
                        <tr class="tr_total">
                            <th><strong>合計</strong></th>
                            <td><span id="confirm-charge-price">0</span>円</td>
                        </tr>
                    </table>
                </div>
                <?= $this->img('bg_charg_card', ['class' => 'bg_charg_card']) ?>
            </div>
            <p class="btn_red btn_plus">
                <?= $this->anchorButton('チャージする', ['id' => 'commit-charge-button']) ?>
            </p>
            <p class="btn_gray btn_loading"><span id="commit-charge-loading">&nbsp;</span></p>
        </div>
    </div>