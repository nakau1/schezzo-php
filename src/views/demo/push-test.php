<?php
/* @var $this \app\views\View */
/* @var $formModel app\models\forms\DemoPushNotify */

use app\widgets\ActiveForm;
use yii\helpers\Html;

$model = new \yii\base\Model();

$this->title = 'プッシュ通知テスト';
$this->registerCss('html, body { background-color: #ffffe4 }');
?>
<header>
    <h1>プッシュ通知テスト</h1>
</header>

<div class="container">
    <?php $form = ActiveForm::begin([
        'id' => 'demo-push-notify',
    ]);
    ?>
    <?= $form->field($formModel, 'uuid')->textInput([
        'class' => 'form-control input-lg',
        'placeholder' => 'UUID',
    ])->label('指定のUUIDのユーザにプッシュ通知を送信します')
    ?>
    <?= $form->field($formModel, 'type')->radioList($formModel->types()) ?>
    <?=
    Html::submitButton('送信', [
        'class' => 'btn btn-lg btn-primary btn-block',
    ])
    ?>
    <?php ActiveForm::end(); ?>
</div>
