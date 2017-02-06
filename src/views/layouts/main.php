<?php
/* @var $this app\views\View */
/* @var $content string */

use app\assets\AppAsset;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

$existsUser = isset($this->user);
$backAction = $existsUser ? $this->getBackAction() : null;

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=375px, user-scalable=no">
        <meta charset="<?= Yii::$app->charset ?>">
        <?= Html::csrfMetaTags() ?>
        <title><?= Html::encode($this->title) ?></title>
        <link rel="index" href="/"/>
        <?= $this->renderFile('@app/views/widgets/tagmanager.php') ?>
        <?php $this->head() ?>
    </head>

    <body<?php if ($this->isGrayBackground): ?> class="body_bggray"<?php endif; ?>>
    <?php $this->beginBody() ?>
    <!----ヘッダー---->
    <header>
        <?php if (!$this->isShowedTitleBar) : ?>
            <?php $this->registerJs("$('body').css('padding-top', $('header').height() + 8);", View::POS_END); ?>
            <p class="pollet_logo"><?= $this->img('pollet_logo') ?></p>
        <?php endif; ?>
        <?php if ($this->isShowedInformationLink && $existsUser): ?>
            <div class="head_mail">
                <a href="<?= Url::to(['information/']) ?>">
                    <p class="ico_head_mail"><?= $this->img('ico_head_mail') ?></p>
                    <?php if ($this->user->hasUnreadInformation): ?><p class="ico_unread"></p><?php endif; ?>
                </a>
            </div>
        <?php endif; ?>
    </header>

    <!----コンテンツ---->
    <div class="contents<?php if ($this->contentsHtmlClass): ?> <?= $this->contentsHtmlClass ?><?php endif; ?>">
        <?php if ($this->isShowedTitleBar): ?>
            <?php $this->registerJs("$('body').css('padding-top', $('.h_box').height() + 20);", View::POS_END); ?>
            <div class="h_box">
                <?php if (!is_null($backAction)): ?>
                    <p class="btn_back">
                        <a href="<?= Url::to($backAction) ?>"><?= $this->img('ico_arrw_back') ?></a>
                    </p>
                <?php endif; ?>
                <p><?= !is_null($this->specifiedTitle) ? $this->specifiedTitle : Html::encode($this->title) ?></p>
            </div>
        <?php endif; ?>
        <?= $content ?>
    </div>

    <?php if ($this->isShowedFooterMenu): ?>
        <!----メニュー---->
        <div class="menu_box">
            <ul class="menu_list">
                <li class="menu_details"><?= Html::a('利用明細', Url::to(['statement/trading'])) ?></li>
                <li class="menu_guide"><?= Html::a('利用ガイド', Url::to(['guide/'])) ?></li>
                <li class="menu_setting"><?= Html::a('設定', Url::to(['default/setting'])) ?></li>
                <li class="menu_mail">
                    <a href="<?= Url::to(['information/']) ?>"><?php if ($existsUser && $this->user->hasUnreadInformation): ?>
                            <p class="ico_unread"></p><?php endif; ?>お知らせ</a>
                </li>
                <?php if ($existsUser && $this->user->isActivatedUser()): ?>
                    <li class="menu_logout"><?= Html::a('ログアウト', Url::to(['auth/sign-out'])) ?></li>
                <?php endif; ?>
                <?php if (!$this->isReleaseMode() && $existsUser): ?>
                    <?= Html::a(
                        'データリセット（デモ用）',
                        Url::to(['demo/delete-me']),
                        ['style' => 'font-size: 14px;']
                    ) ?>
                <?php endif; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php $this->endBody() ?>
    </body>
    </html>
<?php $this->endPage() ?>