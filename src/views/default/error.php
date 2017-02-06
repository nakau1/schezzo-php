<?php
/* @var $this app\views\View */
/* @var $name string */
/* @var $message string */
/* @var $exception Exception */

use yii\helpers\Html;
use yii\helpers\Url;

// お問合わせへのリンク
$inquiryHere = 'こちら';
if (strpos($message, $inquiryHere) !== false) {
    $anchor = Html::a($inquiryHere, ['/inquiry']);
    $message = str_replace($inquiryHere, $anchor, $message);
}

$name = Html::encode($name);
?>
<?php $this->beginPage() ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=375px, user-scalable=no">
        <meta charset="utf-8">
        <title>Pollet</title>
        <?= $this->renderFile('@app/views/widgets/tagmanager.php') ?>
        <style type="text/css">
            body {
                font-family: Verdana, "Hiragino Kaku Gothic ProN", "メイリオ", sans-serif;
                color: #777777;
                line-height: 1.4;
                margin:0px;
            }
            .page_head {
                background: #666666;
                color: #ffffff;
                font-size: 1.2rem;
                text-align: center;
                padding:10px 0;
                margin:0px;
            }
            .contents {
                margin:30px;
            }
            .err_titile {
                margin: 60px auto 0;
                font-size: 1.4rem;
                text-align: center;
                color: #e60012;
                font-weight: bold;
            }
            .err_text {
                margin: 10px auto 30px;
                font-size: 1.0rem;
                text-align: center;
                color: #e60012;
                padding:0 0 30px;
                border-bottom:1px solid #bbb;
            }
            .btn_text {
                text-align: center;
                margin:40px auto;
            }
            .btn_text a {
                color: #ffffff;
                text-decoration: none;
                background: #7ca445;
                padding: 5px 20px;
                border-radius: 50px;
            }
        </style>
    </head>
    <body>
    <?php $this->beginBody() ?>
    <p class="page_head">Pollet</p>
    <div class="contents">
        <p class="err_titile"><?= nl2br($message) ?></p>
        <p class="err_text"><?= $name ?></p>
        <?php if (!$this->isReleaseMode()): ?>
            <p><?= nl2br(Html::encode($exception->getTraceAsString())) ?></p>
        <?php endif; ?>
        <p class="btn_text"><a href="<?= Url::home() ?>">ホームへもどる</a></p>
    </div>
    <?php $this->endBody() ?>
    </body>
    </html>
<?php $this->endPage() ?>