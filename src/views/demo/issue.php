<?php

/* @var $this \app\views\View */
/* @var $formModel app\models\forms\DemoCedynaIdForm */

use app\widgets\ActiveForm;
use yii\helpers\Html;

$this->title = '開発用の発番';
$this->registerCss('html, body { background-color: #ffffe4 }');
?>
<header>
    <h1>開発用の発番</h1>
</header>

<div class="container">
    <?php $form = ActiveForm::begin([
        'id' => 'login-form',
    ]);
    ?>
    <?= $form->field($formModel, 'cedyna_id')->textInput([
        'class' => 'form-control input-lg',
        'placeholder' => 'セディナID (16桁)',
    ])->label('現在のユーザに入力したセディナIDを付与します')
    ?>
    <?=
    Html::submitButton('発番', [
        'class' => 'btn btn-lg btn-primary btn-block',
    ])
    ?>
    <?php ActiveForm::end(); ?>
</div>
