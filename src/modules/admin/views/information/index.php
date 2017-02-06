<?php
/* @var $this app\modules\admin\views\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

use yii\helpers\Html;
use yii\grid\GridView;
use app\helpers\Date;
use yii\grid\GridViewAsset;

$this->registerJsFile('/js/admin/information-index.js', [
    'depends' => [
        GridViewAsset::className(),
    ],
]);

$this->title = 'お知らせ管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="information-index">
    <h1><?= Html::encode($this->title) ?></h1>
    <p>
        <?= Html::a('＋ 新しいお知らせを追加する', ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <?=
    GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            [
                'class'     => 'app\modules\admin\widgets\LinkColumn',
                'attribute' => 'heading',
                'label'     => 'タイトル',
                'url'       => function ($model) { return ['update', 'id' => $model->id]; }
            ],
            [
                'class'     => 'app\modules\admin\widgets\CheckboxColumn',
                'attribute' => 'is_public',
                'label'     => '公開',
            ],
            [
                'attribute' => 'begin_date',
                'format'    => ['date', Date::DISPLAY_ADMIN_INFORMATION_DATTIME_FORMAT],
                'label'     => '開始日',
            ],
            [
                'attribute' => 'end_date',
                'format'    => ['date', Date::DISPLAY_ADMIN_INFORMATION_DATTIME_FORMAT],
                'label'     => '終了日',
            ],
            [
                'class'     => 'app\modules\admin\widgets\CheckboxColumn',
                'attribute' => 'is_important',
                'label'     => '重要',
            ],
            [
                'class'    => 'yii\grid\ActionColumn',
                'template' => '{delete}',
            ],
        ],
    ])
    ?>
    <p class="right text-muted">公開予定の重要なお知らせは、開始日時の5分後を目処にプッシュ通知送信されます</p>
</div>
