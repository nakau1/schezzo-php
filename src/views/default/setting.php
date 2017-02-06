<?php
/* @var $this \app\views\View */

use yii\helpers\Html;
use app\Environment;

/** @var array $envConf */
$envConf = Environment::get();
$cedynaMyPageLoginUrl         = $envConf['cedynaMyPageUrls']['login'];
$cedynaMyPagePasswordResetUrl = $envConf['cedynaMyPageUrls']['passwordReset'];

$this->title = '設定';
?>
<div class="main_box">
    <p class="btn_setting btn_01"><?= Html::a('<span>登録情報変更</span>', $cedynaMyPageLoginUrl, ['target' => '_blank']) ?></p>
    <p class="btn_setting btn_03"><?= Html::a('<span>パスワード変更</span>', $cedynaMyPagePasswordResetUrl, ['target' => '_blank']) ?></p>
    <p class="btn_setting btn_02"><?= Html::a('<span>カード停止・再開</span>',  $cedynaMyPageLoginUrl, ['target' => '_blank']) ?></p>
</div>