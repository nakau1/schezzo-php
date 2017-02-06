<?php
/* @var $this \app\views\View */
/* @var $linkSp string */
/* @var $linkDev string */

use yii\helpers\Html;

$this->registerCss('html, body { background-color: #ffffe4 }');
?>
<h1>デモ用外部認証サイト</h1>

<?=
Html::button('認証する', [
    'class' => 'btn btn-normal btn-lg btn-block center-block',
    'onclick' => "location.href='{$linkSp}'"
])
?>
<?=
Html::button('認証する（開発用）', [
    'class' => 'btn btn-normal btn-lg btn-block center-block',
    'onclick' => "location.href='{$linkDev}'"
])
?>
