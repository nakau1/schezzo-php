<?php
/* @var $this app\modules\admin\views\View */
/* @var $model app\modules\admin\models\Information */
/* @var $form yii\widgets\ActiveForm */

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\modules\admin\views\View;
use app\modules\admin\extensions\JQueryDateTimePicker;

?>
<div class="information-form">
    <?php $form = ActiveForm::begin(); ?>
    <div class="row">
        <div class="col-md-8">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h3 class="panel-title">掲載期間</h3>
                </div>
                <div class="panel-body">
                    <div class="col-md-6">
                        <?= $form->field($model, 'begin_date')->widget(JQueryDateTimePicker::className()) ?>
                    </div>
                    <div class="col-md-6">
                        <?= $form->field($model, 'end_date')->widget(JQueryDateTimePicker::className()) ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h3 class="panel-title">お知らせ設定</h3>
                </div>
                <div class="panel-body">
                    <?= $form->field($model, 'sends_push')->checkbox() ?>
                    <?= $form->field($model, 'is_important')->checkbox() ?>
                    <?= $form->field($model, 'is_public')->checkbox() ?>
                    <div class="btn-toolbar">
                        <?php if ($model->isNewRecord): ?>
                            <?= Html::submitButton('作成する', ['class' => 'btn btn-success']) ?>
                        <?php else: ?>
                            <?= Html::submitButton('更新する', ['class' => 'btn btn-primary']) ?>
                            <?= Html::a('削除する', ['information/delete', 'id' => $model->id], [
                                'class' => 'btn btn-danger',
                                'data-confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'),
                                'data-method' => 'post',
                                'data-pjax' => '0',
                            ]) ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h3 class="panel-title">お知らせ内容</h3>
                </div>
                <div class="panel-body">
                    <?= $form->field($model, 'heading')->textInput(['maxlength' => true]) ?>
                    <?= $form->field($model, 'body')->textarea(['rows' => 16]) ?>
                </div>
            </div>
        </div>
    </div>
    <?php ActiveForm::end(); ?>
</div>
