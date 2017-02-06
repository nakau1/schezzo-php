<?php
/* @var $this app\modules\admin\views\View */
/* @var $model app\modules\admin\models\Information */

use yii\helpers\Html;

$this->title = $model->isNewRecord ? '新しいお知らせの追加' : '既存のお知らせの更新';
$this->params['breadcrumbs'][] = ['label' => 'お知らせ管理', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="information-create">
    <h1><?= Html::encode($this->title) ?></h1>
    <?=
    $this->render('_form', [
        'model' => $model,
    ])
    ?>
</div>
