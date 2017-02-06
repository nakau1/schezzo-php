<?php
/* @var $this \app\views\View */
/* @var $informations \app\models\Information[] */

use app\helpers\Format;
use yii\helpers\Html;

$this->isGrayBackground = false;
$this->title = 'お知らせ';
?>
<?php if (!$informations): ?>
    <div class="alert alert-warning">お知らせはありません</div>
<?php endif; ?>
<div class="info_list_box">
    <?php foreach ($informations as $information): ?>
    <dl>
        <dt>
            <?= Format::formattedJapaneseDate($information->begin_date) ?>
            <?php if (!$information->isOpened): ?><span class="ico_unread"></span><?php endif; ?>
        </dt>
        <dd><?= Html::a(Html::encode($information->heading), ['information/detail', 'id' => $information->id]) ?></dd>
    </dl>
    <?php endforeach; ?>
</div>
