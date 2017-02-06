<?php
/* @var $this \app\views\View */
/* @var $formModel \app\models\forms\IssuanceForm */

use app\assets\AppJQueryAsset;
use app\widgets\ActiveForm;
use yii\helpers\Html;

AppJQueryAsset::register($this);
$this->registerCssFile('/css/app/privacy-policy.css');
$this->registerJsFile('/js/app/issuance.js', [
    'depends' => [
        AppJQueryAsset::className(),
    ],
]);

$this->title = 'カード発行手続き';
?>

<p class="mt40 text_start_issuance">
    続いてカード発行手続きをはじめます。<br>メールアドレスのご登録をお願いします。
</p>
<?php $form = ActiveForm::begin(['id' => 'issuance']); ?>
    <div class="main_box">
        <p class="h_input mt20">メールアドレス</p>
        <?= $form->field($formModel, 'mail_address')->textInput([
            'class'     => 'input_style',
            'autofocus' => true,
        ])->label(false)
        ?>
        <p class="btn_terms right mt30"><?= Html::a('カード利用規約を読む >', '#term_modal', ['class' => 'fancybox']) ?></p>
        <p class="btn_red btn_next mt15">
            <?=
            $this->anchorButton('次へ', [
                'onclick' => '$("#issuance").submit()',
            ])
            ?>
        </p>
    </div>
    <!-- submitボタンがないとenterでフォームを送信できないことがある -->
    <input type="submit" style="visibility:hidden;">
<?php ActiveForm::end(); ?>

<div style="display:none;">
    <div id="term_modal" class="issuance_term_modal">
        <div class="card_terms_box">
            <?= $this->renderFile('@app/views/widgets/card-terms.php') ?>
        </div>
        <p class="btn_modal_close"><a class="close_modal">閉じる</a></p>
    </div>
</div>