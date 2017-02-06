<?php
/* @var $this \app\views\View */
/* @var $showTutorial boolean */

use app\assets\StartPageAsset;
use yii\helpers\Html;

StartPageAsset::register($this);

$this->isGrayBackground = false;
$this->isShowedTitleBar = false;
$this->title = 'はじめる';
?>
<div class="img_top_main"><?= $this->img('img_top_main') ?></div>
<div class="main_box">
    <p class="btn_terms center mb15">
        <?= Html::a('アプリ利用規約', ['/terms']) ?>と<?= Html::a('プライバシーポリシー', ['privacy-policy/']) ?><br>
        に同意の上、お進みください。
    </p>
    <p class="btn_red btn_begin mb20"><?= Html::a('同意してはじめる', ['charge/list']) ?></p>
    <p class="btn_login_red center"><?= Html::a('ログイン', ['auth/sign-in']) ?></p>
</div>

<?php if ($showTutorial): ?>
    <!--//チュートリアルモーダル-->
    <div class="modal_box" style="display:block;">
        <div class="modal_box_inner">
            <div class="modal_tutorial_box">
                <?= $this->img('tutorial/img_tutorial_00', ['class' => 'img_tutorial tutorial_00']) ?>
                <p class="btn_modal_close"><a class="close_modal">閉じる</a></p>
            </div>
            <div class="modal_tutorial_box">
                <?= $this->img('tutorial/img_tutorial_01', ['class' => 'img_tutorial tutorial_01']) ?>
                <p class="btn_modal_close"><a class="close_modal">閉じる</a></p>
            </div>
            <div class="modal_tutorial_box">
                <?= $this->img('tutorial/img_tutorial_02', ['class' => 'img_tutorial tutorial_02']) ?>
                <p class="btn_modal_close"><a class="close_modal">閉じる</a></p>
            </div>
            <div class="modal_tutorial_box">
                <?= $this->img('tutorial/img_tutorial_03', ['class' => 'img_tutorial tutorial_03']) ?>
                <p class="btn_modal_close"><a class="close_modal">閉じる</a></p>
            </div>
            <div class="modal_tutorial_box">
                <?= $this->img('tutorial/img_tutorial_04', ['class' => 'img_tutorial tutorial_04']) ?>
                <p class="btn_modal_close"><a class="close_modal">閉じる</a></p>
                <p class="btn_last"><a class="close_modal"></a></p>
            </div>
        </div>
    </div>
<?php endif; ?>