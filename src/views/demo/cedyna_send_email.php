<?php
/* @var $this \app\views\View */
/* @var $error string */

use yii\helpers\Html;

// url: Contents/REC/EnterEmailAddress.aspx
$this->title = 'お客様メールアドレス入力';
?>

<?php if (isset($error)) : ?>
    <!-- 本番にはない -->
    <div class="alert alert-danger"><?php echo Html::encode($error); ?></div>
<?php endif; ?>

<form method="post" action="./cedyna-send-email-complete" onkeypress="" id="form">
    <div class="container-main">
        <div class="content-page-title">
            お申込用空メールアドレス送信
        </div>
        <div class="panel-contents">
            <div class="panel-details">
                <div class="panel panel-info">
                    <div class="panel-heading panel-caption">利用規約と、個人情報の取扱いに関する同意条項をご確認の上、ご連絡用のメールアドレスをご入力ください。</div>
                    <div class="panel-body panel-content-body">
                        <div id="MainContent_DetailContent_cDetail_DetailMain" class="detail detail-container">
                            <div id="MainContent_DetailContent_cDetail_DetailMainLeft"
                                 class="detail detail-container-left">
                                <div id="MainContent_DetailContent_cDetail_DetailHeader" class="detail detail-header"
                                     style="overflow: visible;">
                                    <table id="MainContent_DetailContent_cDetail_mail_Detail" class="TableDetail"
                                           style="width:100%;">
                                        <tbody>
                                        <tr class="TableDetailTr">
                                            <td class="Control_Caption Reauired_Caption" rowspan="2"><span
                                                    class="caption-span signal"><span
                                                        class="label label-warning">必須</span></span><span
                                                    class="caption-span caption">メールアドレス</span></td>
                                            <td class="Control_Item"><input
                                                    name="ctl00$ctl00$MainContent$DetailContent$cDetail$mail$Dtm_mail_address$TextBox"
                                                    type="text" maxlength="50" size="51"
                                                    id="MainContent_DetailContent_cDetail_mail_Dtm_mail_address_TextBox"
                                                    tabindex="3010"
                                                    class="validate[custom[htmlTag],required,maxSize[50],custom[email]]  Reauired Entry DetailItem1"
                                                    text-type="Varchar" placeholder="ご連絡先用のメールアドレスを入力してください。"
                                                    style="text-align:left;padding-left:2px;" autocomplete="off"
                                                    oncopy="return false;"></td>
                                        </tr>
                                        <tr class="TableDetailTr">
                                            <td class="Control_Item"><input
                                                    name="ctl00$ctl00$MainContent$DetailContent$cDetail$mail$Dtm_mail_address2$TextBox"
                                                    type="text" maxlength="50" size="51"
                                                    id="MainContent_DetailContent_cDetail_mail_Dtm_mail_address2_TextBox"
                                                    tabindex="3010"
                                                    class="validate[custom[htmlTag],required,maxSize[50],custom[email],equals[MainContent_DetailContent_cDetail_mail_Dtm_mail_address_TextBox]]  Reauired Entry DetailItem1"
                                                    text-type="Varchar" placeholder="ご確認のためもう一度入力してください。"
                                                    style="text-align:left;padding-left:2px;" autocomplete="off"
                                                    oncopy="return false;"></td>
                                        </tr>
                                        <tr class="TableDetailTr">
                                            <td class="Control_Item dummy-label"><span
                                                    id="MainContent_DetailContent_cDetail_mail_Dtm_dummy_Label_Dtm_dummy"
                                                    class=" Label_Dtm_dummy  Entry DetailItem1"> </span></td>
                                            <td class="Control_Item"><span
                                                    id="MainContent_DetailContent_cDetail_mail_Dtm_guide_Label_Dtm_guide"
                                                    class=" Label_Dtm_guide  Entry DetailItem1">※ドメイン指定をされている場合は、「@prepaid-cedyna.jp」からのメールを受信できるようにしておいてください。</span>
                                            </td>
                                            <td class="Control_Item">
                                            </td>
                                        </tr>
                                        <tr class="TableDetailTr">
                                            <td class="Control_Item"><span
                                                    id="MainContent_DetailContent_cDetail_mail_Dtm_dummy2_Label_Dtm_dummy2"
                                                    class=" Label_Dtm_dummy2  Entry DetailItem1"></span>
                                            </td>
                                            <td class="Control_Item"><span
                                                    id="MainContent_DetailContent_cDetail_mail_Dtm_guide2_Label_Dtm_guide2"
                                                    class=" Label_Dtm_guide2  Entry DetailItem1">※カード発行には当社所定の確認がございます。確認の結果、ご希望に添えない場合がございます、あらかじめご了承ください。</span>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="panel panel-info">
                    <div class="panel-heading panel-toggle panel-caption">
                        <span class="panel-caption">利用規約・個人情報の取扱いに関する同意条項</span>
                        <i class="fa fa-chevron-circle-down panel-toggle" aria-hidden="true"></i>
                    </div>
                    <div class="panel-body panel-content-body" style="display: none;">
                        <div class="page-guide well">
                            <iframe src="../../File/html/ConsentProvision_MemberAgreement.html" name="sample"
                                    width="100%" height="300"></iframe>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="panel-buttons">
            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-spacing: 0px;">
                <tbody>
                <tr>
                    <td align="right" style="vertical-align: top;">
                        <input type="submit" name="ctl00$ctl00$MainContent$DetailButtonContent$BtnDecision" value="次へ"
                               onclick="return InputValidating(this,'Entry');"
                               id="MainContent_DetailButtonContent_BtnDecision" tabindex="-1"
                               class="Button button button-rounded button-flat-primary  Decision"
                               onkeydown=""
                               style="margin-bottom: 3px;">
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </div><!-- /.container -->
</form>