<?php
/* @var $this \app\views\View */
/* @var $information \app\models\Information */

use app\helpers\Format;
use yii\helpers\Html;

$this->backAction = ['information/'];
$this->isGrayBackground = false;
$this->title = 'お知らせ';
?>
<div class="info_page_head">
    <p><?= Format::formattedJapaneseDate($information->begin_date) ?></p>
    <h1><?= Html::encode($information->heading) ?></h1>
</div>
<div class="info_page_box">
    <?= nl2br(Html::encode($information->body)) ?>
</div>