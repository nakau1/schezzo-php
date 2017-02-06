<?php
/* @var $this \app\views\View */

$this->backAction = ['guide/'];
$this->isGrayBackground = false;
$this->title = 'カード利用規約';
$this->registerCssFile('/css/app/privacy-policy.css');
?>
<?= $this->renderFile('@app/views/widgets/card-terms.php') ?>
