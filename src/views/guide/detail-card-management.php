<?php
/* @var $this \app\views\View */

use yii\helpers\Html;
use app\Environment;

$env = Environment::get();
$url = $env['cedynaMyPageUrls']['memberSiteLink'];

$this->backAction = ['guide/'];
$this->isGrayBackground = false;
$this->title = 'カードの停止/再開/解約';
?>
<div class="main_box card_details_box">
    <h2>カードの停止/再開/解約</h2>
    <div class="text_card_details_box">
        <p>カードの停止/再開/は、<?= Html::a('Pollet会員専用サイト', $url, ['target' => '_blank']) ?>から設定ができます。<br>
            また、解約はセディナアンサーセンター（電話：03-4330-1377／受付時間：9：30～17：00／1月1日休）までお問い合わせください。</p>
    </div>
</div>