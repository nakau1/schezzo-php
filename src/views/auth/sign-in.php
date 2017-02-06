<?php
/* @var $this \app\views\View */
/* @var $formModel \app\models\forms\SignInForm */

use app\assets\AppJQueryAsset;
use app\widgets\ActiveForm;
use yii\helpers\Html;
use app\Environment;

AppJQueryAsset::register($this);
$this->registerJsFile('/js/app/auth-sign-in.js', [
    'depends' => [
        AppJQueryAsset::className(),
    ],
]);
if ($this->user->isSignOut()) {
    $this->backAction = false;
}
/** @var array $envConf */
$envConf = Environment::get();
$cedynaMyPagePasswordResetUrl = $envConf['cedynaMyPageUrls']['passwordReset'];

$this->title = $this->user->isIssued() ? 'カード認証' : 'ログイン';
?>
<div class="main_box">
    <?php
    $form = ActiveForm::begin([
        'id' => 'login-form',
    ]);
    ?>
    <p class="h_input mt80">会員番号</p>
    <input type="tel" title="会員番号の1~4桁"   class="cedyna-id-piece fl_l center" required maxlength="4" autofocus>
    <input type="tel" title="会員番号の5~8桁"   class="cedyna-id-piece fl_l center" required maxlength="4">
    <input type="tel" title="会員番号の9~12桁"  class="cedyna-id-piece fl_l center" required maxlength="4">
    <input type="tel" title="会員番号の13~16桁" class="cedyna-id-piece fl_l center" required maxlength="4">
    <div class="clear"></div>
    <?=
    // 実際に送るフィールド。値はJSで組み立てる
    $form->field($formModel, 'cedyna_id')->hiddenInput([
        'id' => 'cedyna-id'
    ])->label(false);
    ?>
    <p class="text_notice right mb15">
        ※カード裏面16桁の数字
    </p>
    <?php if ($formModel->isNecessityInputPassword()): ?>
        <p class="h_input mt30">パスワード</p>
        <?=
        $form->field($formModel, 'password')->passwordInput([
            'class'    => 'input_style',
            'required' => true,
        ])->label(false)
        ?>
        <p class="btn_terms right mb15"><?= Html::a('> パスワードを忘れた方', $cedynaMyPagePasswordResetUrl, ['target' => '_blank']) ?></p>
    <?php endif; ?>
    <p class="btn_red btn_login mt30">
        <?=
        $this->anchorButton($this->user->isIssued() ? '認証' : 'ログイン', [
            'class' => 'login-form-submit',
        ])
        ?>
    </p>
    <!-- submitボタンがないとenterでフォームを送信できないことがある -->
    <input type="submit" style="visibility:hidden;">
    <?php ActiveForm::end() ?>
</div>