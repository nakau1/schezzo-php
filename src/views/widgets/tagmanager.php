<?php
/* @var $this \app\views\View */
use yii\helpers\Html;

?>
<meta name="pollet_user_id" content="<?= Html::encode($this->user->id ?? 'null') ?>">
<meta name="registration_status" content="<?= Html::encode($this->user->registration_status ?? 'null') ?>">
<!-- Google Tag Manager -->
<?php if ($this->isReleaseMode()) : ?>
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
            new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
            j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
            'https://www.googletagmanager.com/gtm.js?id='+i+dl+ '&gtm_auth=CSPIOi3oAVM8MmzmcReoWw&gtm_preview=env-2&gtm_cookies_win=x';f.parentNode.insertBefore(j,f);
        })(window,document,'script','dataLayer','GTM-NB7R2KL');</script>
<?php elseif ($this->isDemoMode()) : ?>
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
            new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
            j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
            'https://www.googletagmanager.com/gtm.js?id='+i+dl+ '&gtm_auth=f-ex18-lrz3iYP5Ebt9ZaQ&gtm_preview=env-5&gtm_cookies_win=x';f.parentNode.insertBefore(j,f);
        })(window,document,'script','dataLayer','GTM-NB7R2KL');</script>
<?php elseif ($this->isTestMode()) : ?>
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
            new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
            j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
            'https://www.googletagmanager.com/gtm.js?id='+i+dl+ '&gtm_auth=YPeFRFo_bdkUt_x65074IA&gtm_preview=env-6&gtm_cookies_win=x';f.parentNode.insertBefore(j,f);
        })(window,document,'script','dataLayer','GTM-NB7R2KL');</script>
<?php endif; ?>
<!-- End Google Tag Manager -->
