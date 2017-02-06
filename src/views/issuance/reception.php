<?php
/* @var $this \app\views\View */

use yii\bootstrap\Html;

$this->title = 'カード発行手続き';
?>
    <div class="main_box">
        <p class="img_card_comp center"><?= $this->img('img_card_comp') ?></p>
        <p class="text_card_comp">ご登録のメールアドレスに認証メールを<br>
            お送りしました。</p>
        <p class="text_card_comp mt20">手続きを進めてください。</p>
        <?php if (!$this->isReleaseMode()): ?>
            <p><?= Html::a('発番する(開発用)', ['demo/issue']) ?></p>
        <?php endif; ?>
    </div>
