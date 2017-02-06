<?php
/* @var $this yii\web\View */
/* @var $cardValue int */
/* @var $rows array */

// url: Contents/MBR/TopPage.aspx
$this->title = 'トップページ - マイページ';

$js = <<<JS
$(".activate-card").click(function() {
        var card_id = $(this).attr('card-id');
        
        $.ajax({
            url: "cedyna-my-page-activate",
            type: 'get',
            async: false,
            data: {
                'cardId': card_id,
            },
            success: function(data) {
                location.reload();
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                alert(errorThrown);
            }
        });
});
JS;
$this->registerJs($js, \app\views\View::POS_END);
?>

<form method="post" action="" onkeypress="" id="form">
    <nav class="navbar navbar-inverse navbar-fixed-top">
        <div class="container-header">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed cancelValidate" data-toggle="collapse"
                        data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a href="/" id="site_name" class="navbar-brand" target="_blank">
                    <img class="site_logo_image" src="../../Images/SystemLogo.png"><span
                        class="site_logo_caption">マイページ</span>
                </a>
            </div>
            <div id="navbar" class="collapse navbar-collapse">
                <ul class="nav navbar-nav navbar-right">
                    <li><a class="header-button-container"> <input type="submit" name="ctl00$BtnTopLink" value="トップページ"
                                                                   onclick=""
                                                                   id="BtnTopLink" title="トップページ"
                                                                   class="header-button cancelValidate"> </a></li>
                    <li id="MenuGroupCharge" class="dropdown">
                        <a href="#" class="header-button-container" data-toggle="dropdown" role="button"
                           aria-haspopup="true" aria-expanded="true"><input type="submit" name="ctl00$Button2"
                                                                            value=" バリューチャージ" id="Button2"
                                                                            class="header-button"></a>
                        <ul class="dropdown-menu dropdown-menu-color">
                            <li><a class="header-dropdown-button-container"> <input type="submit"
                                                                                    name="ctl00$BtnCreditCharge"
                                                                                    value="クレジット" id="BtnCreditCharge"
                                                                                    title="クレジット"
                                                                                    class="header-dropdown-button"
                                                                                    link-url="CreditCharge.aspx"
                                                                                    authentication-key="VCOqGCTCsFoIHhcqVG3ABrpo7tt9jE37">
                                </a></li>
                            <li><a class="header-dropdown-button-container"> </a></li>
                            <li><a class="header-dropdown-button-container"> <input type="submit"
                                                                                    name="ctl00$BtnPayeasyCharge"
                                                                                    value="ペイジー" id="BtnPayeasyCharge"
                                                                                    title="ペイジー"
                                                                                    class="header-dropdown-button"
                                                                                    link-url="PayeasyCharge.aspx"
                                                                                    authentication-key="VCOqGCTCsFrKPX8N9nAJYWv5d0qWwJGd">
                                </a></li>
                        </ul>
                    </li>
                    <li id="MenuGroupOther" class="dropdown">
                        <a href="#" class="header-button-container" data-toggle="dropdown" role="button"
                           aria-haspopup="true" aria-expanded="true"><input type="submit" name="ctl00$Button1"
                                                                            value=" 各種メニュー" id="Button1"
                                                                            class="header-button"></a>
                        <ul class="dropdown-menu dropdown-menu-color">
                            <li><a class="header-dropdown-button-container"> <input type="submit"
                                                                                    name="ctl00$BtnChangeAttribute"
                                                                                    value="ご登録情報照会・変更"
                                                                                    id="BtnChangeAttribute"
                                                                                    title="ご登録情報照会・変更"
                                                                                    class="header-dropdown-button multiple-auth-button"
                                                                                    link-url="ChangeAttribute.aspx"
                                                                                    authentication-key="VCOqGCTCsFo9QiH6ZSdIVIr2ZGsb+T0H">
                                </a></li>
                            <li><a class="header-dropdown-button-container"> <input type="submit"
                                                                                    name="ctl00$BtnUsageDetailsBalance"
                                                                                    value="ご利用明細・残高照会"
                                                                                    id="BtnUsageDetailsBalance"
                                                                                    title="ご利用明細・残高照会"
                                                                                    class="header-dropdown-button"
                                                                                    link-url="UsageDetailsBalance.aspx"
                                                                                    authentication-key="VCOqGCTCsFqwwOq+0Oii9vujdvmcVu9r">
                                </a></li>
                            <li><a class="header-dropdown-button-container"> <input type="submit"
                                                                                    name="ctl00$BtnChangeCardStatus"
                                                                                    value="カードご利用一時停止・再開"
                                                                                    id="BtnChangeCardStatus"
                                                                                    title="カードご利用一時停止・再開"
                                                                                    class="header-dropdown-button"
                                                                                    link-url="ChangeCardStatus.aspx"
                                                                                    authentication-key="VCOqGCTCsFpXU6ZfetOWQa0p8U5VWe2H"></a>
                            </li>
                            <li id="Separator" role="separator" class="divider"></li>
                            <li><a class="header-dropdown-button-container"> <input type="submit"
                                                                                    name="ctl00$BtnPinChange"
                                                                                    value="カード暗証番号変更" id="BtnPinChange"
                                                                                    title="カード暗証番号変更"
                                                                                    class="header-dropdown-button multiple-auth-button"
                                                                                    link-url="PinChange.aspx"
                                                                                    authentication-key="VCOqGCTCsFpX8qAyA/r6ZY1QHY6EGgZN"></a>
                            </li>
                            <li><a class="header-dropdown-button-container"> <input type="submit"
                                                                                    name="ctl00$BtnPasswordChange"
                                                                                    value="マイページログインパスワード変更"
                                                                                    id="BtnPasswordChange"
                                                                                    title="Myページログインパスワード変更"
                                                                                    class="header-dropdown-button multiple-auth-button"
                                                                                    link-url="PasswordChange.aspx"
                                                                                    authentication-key="VCOqGCTCsFreS8e9hVPXkX3+wLYohZ+/">
                                </a></li>
                        </ul>
                    </li>
                    <li><a class="header-button-container"> <input type="submit" name="ctl00$BtnLogout" value="ログアウト"
                                                                   onclick=""
                                                                   id="BtnLogout" title="ログアウト"
                                                                   class="header-button cancelValidate"> </a></li>
                </ul>
            </div>
        </div><!--/.nav-collapse -->
    </nav>
    <!--  -->
    <div class="container-main">
        <div class="content-page-title">
            トップページ
        </div>
        <!--  -->
        <div class="container-main row">
            <div class="col-md-12 col-lg-12">
                <div class="panel panel-primary content-cards">
                    <div class="panel-heading">
                        <span class="panel-caption">ご利用カード情報</span>
                        <i class="fa fa-chevron-circle-up panel-toggle" aria-hidden="true"></i>
                    </div>
                    <div class="panel-body">
                        <div id="container-card-list" class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                <tr>
                                    <td style="width: 134px"></td>
                                    <td></td>
                                    <td>カード番号</td>
                                    <td>カードステータス</td>
                                    <td>有効期限</td>
                                    <td>発行日</td>
                                    <td>停止日</td>
                                    <td>状態</td>
                                </tr>
                                </thead>
                                <tbody>
                                <?php $i = 1; foreach ($rows as $cardId => $activated): ?>
                                <tr>
                                    <td>
                                        <?php if (!$activated): ?>
                                        <a id="MainContent_CardList_HyperLink<?= $i++ ?>_0" tabindex="-1" class="activate-card button button-rounded button-flat-caution button-small" card-id="<?= $cardId ?>">アクティベート</a>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <img id="MainContent_CardList_Image1_0"
                                             src="../../Files/Card_Image/0001/0001.png"
                                             style="height:64px;width:102px;">
                                    </td>
                                    <td>
                                        ************<?= rand(1000, 9999) ?>
                                    </td>
                                    <td>
                                        <?= $activated ? '有効' : 'アクティベート前' ?>
                                    </td>
                                    <td>
                                        09/2021
                                    </td>
                                    <td>
                                        2016/09/21
                                    </td>
                                    <td>
                                    </td>
                                    <td>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-12 col-lg-12 row">
                <div class="container-unit col-lg-12">
                    <div class="panel panel-info">
                        <div class="panel-heading">
                            <span class="panel-caption">残高情報</span>
                            <i class="fa fa-chevron-circle-up panel-toggle" aria-hidden="true"></i>
                        </div>
                        <div class="panel-body">
                            <div><span id="dispay-time">2016年10月04日 13時21分</span>&nbsp;現在</div>
                            <div><span id="member-name">ぽれっと 太郎</span>&nbsp;様の現在のご利用可能残高は&nbsp;<span id="member-balance"><?php echo $cardValue; ?></span>&nbsp;円です。
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-12 col-lg-12 row">
                <div class="container-unit col-lg-12">
                    <div class="panel panel-success">
                        <div class="panel-heading">
                            <span class="panel-caption">お知らせ</span>
                            <i class="fa fa-chevron-circle-up panel-toggle" aria-hidden="true"></i>
                        </div>
                        <div class="panel-body">
                            <ul>
                                <li>
                                    <div class="notice_content_title">
                                        <span></span>&nbsp;<a class="notice_content_title_link"><span
                                                class="content_title content-title-notice">ご利用いただけない加盟店について</span></a>
                                    </div>
                                    <div class="notice_content_contents alert alert-info" style="display: none;">
                                        <span>POINT WALLET VISA PREPAIDでは、一部ご利用いただけないVISA加盟店がございます、ご利用の際はお気を付けください。<br>【ご利用いただけない可能性のある加盟店】<br>・電気料金、携帯電話料金など、カード番号をご登録いただき、継続的にお支払いが発生する加盟店<br>・高速道路料金、ガソリンスタンド</span>
                                        <div class="notice_content_contents_link"></div>
                                    </div>
                                </li>

                            </ul>

                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-12 col-lg-12 row">
                <div class="container-unit col-lg-12">
                    <div class="panel panel-warning">
                        <div class="panel-heading">
                            <span class="panel-caption">リンク</span>
                            <i class="fa fa-chevron-circle-up panel-toggle" aria-hidden="true"></i>
                        </div>
                        <div class="panel-body">
                            <a id="MainContent_link_member_agreement" title="利用規約"
                               class="button button-pill button-flat-primary" href=""
                               target="_self">利用規約</a>
                            <a id="MainContent_link_consent_provision" title="個人情報の取扱いに関する同意条項"
                               class="button button-pill button-flat-primary" href=""
                               target="_self">個人情報の取扱いに関する同意条項</a>
                            <a id="MainContent_link_faq" title="FAQ" class="button button-pill button-flat-primary"
                               href="" target="_self">FAQ</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div><!-- /.container -->
</form>