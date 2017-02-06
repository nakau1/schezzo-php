<?php
/* @var $this yii\web\View */
use app\models\TradingHistory;

/* @var $cardNumber string */
/* @var $fromDate string */
/* @var $toDate string */
/* @var $tradingHistories TradingHistory[] */

// url: Contents/MBR/UsageDetailsBalance.aspx
$this->title = 'ご利用明細・残高照会 - マイページ';
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
                    <li><a class="header-button-container"> <input type="submit" name="ctl00$ctl00$BtnTopLink"
                                                                   value="トップページ"
                                                                   onclick=""
                                                                   id="BtnTopLink" title="トップページ"
                                                                   class="header-button cancelValidate"> </a></li>
                    <li id="MenuGroupCharge" class="dropdown">
                        <a href="#" class="header-button-container" data-toggle="dropdown" role="button"
                           aria-haspopup="true" aria-expanded="true"><input type="submit" name="ctl00$ctl00$Button2"
                                                                            value=" バリューチャージ" id="Button2"
                                                                            class="header-button"></a>
                        <ul class="dropdown-menu dropdown-menu-color">
                            <li><a class="header-dropdown-button-container"> <input type="submit"
                                                                                    name="ctl00$ctl00$BtnCreditCharge"
                                                                                    value="クレジット" id="BtnCreditCharge"
                                                                                    title="クレジット"
                                                                                    class="header-dropdown-button"
                                                                                    link-url="CreditCharge.aspx"
                                                                                    authentication-key="VCOqGCTCsFoIHhcqVG3ABrpo7tt9jE37">
                                </a></li>
                            <li><a class="header-dropdown-button-container"> </a></li>
                            <li><a class="header-dropdown-button-container"> <input type="submit"
                                                                                    name="ctl00$ctl00$BtnPayeasyCharge"
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
                           aria-haspopup="true" aria-expanded="true"><input type="submit" name="ctl00$ctl00$Button1"
                                                                            value=" 各種メニュー" id="Button1"
                                                                            class="header-button"></a>
                        <ul class="dropdown-menu dropdown-menu-color">
                            <li><a class="header-dropdown-button-container"> <input type="submit"
                                                                                    name="ctl00$ctl00$BtnChangeAttribute"
                                                                                    value="ご登録情報照会・変更"
                                                                                    id="BtnChangeAttribute"
                                                                                    title="ご登録情報照会・変更"
                                                                                    class="header-dropdown-button multiple-auth-button"
                                                                                    link-url="ChangeAttribute.aspx"
                                                                                    authentication-key="VCOqGCTCsFo9QiH6ZSdIVIr2ZGsb+T0H">
                                </a></li>
                            <li><a class="header-dropdown-button-container"> <input type="submit"
                                                                                    name="ctl00$ctl00$BtnUsageDetailsBalance"
                                                                                    value="ご利用明細・残高照会"
                                                                                    id="BtnUsageDetailsBalance"
                                                                                    title="ご利用明細・残高照会"
                                                                                    class="header-dropdown-button"
                                                                                    link-url="UsageDetailsBalance.aspx"
                                                                                    authentication-key="VCOqGCTCsFqwwOq+0Oii9vujdvmcVu9r">
                                </a></li>
                            <li><a class="header-dropdown-button-container"> <input type="submit"
                                                                                    name="ctl00$ctl00$BtnChangeCardStatus"
                                                                                    value="カードご利用一時停止・再開"
                                                                                    id="BtnChangeCardStatus"
                                                                                    title="カードご利用一時停止・再開"
                                                                                    class="header-dropdown-button"
                                                                                    link-url="ChangeCardStatus.aspx"
                                                                                    authentication-key="VCOqGCTCsFpXU6ZfetOWQa0p8U5VWe2H"></a>
                            </li>
                            <li id="Separator" role="separator" class="divider"></li>
                            <li><a class="header-dropdown-button-container"> <input type="submit"
                                                                                    name="ctl00$ctl00$BtnPinChange"
                                                                                    value="カード暗証番号変更" id="BtnPinChange"
                                                                                    title="カード暗証番号変更"
                                                                                    class="header-dropdown-button multiple-auth-button"
                                                                                    link-url="PinChange.aspx"
                                                                                    authentication-key="VCOqGCTCsFpX8qAyA/r6ZY1QHY6EGgZN"></a>
                            </li>
                            <li><a class="header-dropdown-button-container"> <input type="submit"
                                                                                    name="ctl00$ctl00$BtnPasswordChange"
                                                                                    value="マイページログインパスワード変更"
                                                                                    id="BtnPasswordChange"
                                                                                    title="Myページログインパスワード変更"
                                                                                    class="header-dropdown-button multiple-auth-button"
                                                                                    link-url="PasswordChange.aspx"
                                                                                    authentication-key="VCOqGCTCsFreS8e9hVPXkX3+wLYohZ+/">
                                </a></li>
                        </ul>
                    </li>
                    <li><a class="header-button-container"> <input type="submit" name="ctl00$ctl00$BtnLogout"
                                                                   value="ログアウト"
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
            ご利用明細・残高照会
        </div>
        <div class="panel-contents">
            <div class="panel-search">
                <fieldset id="MainContent_SearchGroup" class="search-group-area">
                    <div id="toggle-search"><i class="fa fa-2x fa-caret-square-o-up" aria-hidden="true"></i></div>

                    <legend>ご利用明細・残高の検索条件を入力してください。</legend>
                    <div class="Search_Area_Input">
                        <table id="MainContent_SearchContent_cSearch_Search" class="Search_Container"
                               style="width:100%;">
                            <tbody>
                            <tr>
                                <td class="Control_Caption">カード番号</td>
                                <td class="Control_Item" style="white-space:nowrap;">
                                    <div class="select2-container validate[custom[htmlTag]] noRequiredDdl Search"
                                         id="s2id_MainContent_SearchContent_cSearch_Srh_card_id_ddlList"
                                         style="display: block;"><a href="javascript:void(0)"
                                                                    class="select2-choice select2-default"
                                                                    tabindex="-1"> <span class="select2-chosen"
                                                                                         id="select2-chosen-1">未選択</span><abbr
                                                class="select2-search-choice-close"></abbr> <span class="select2-arrow"
                                                                                                  role="presentation"><b
                                                    role="presentation"></b></span></a><label for="s2id_autogen1"
                                                                                              class="select2-offscreen"></label><input
                                            class="select2-focusser select2-offscreen" type="text" aria-haspopup="true"
                                            role="button" aria-labelledby="select2-chosen-1" id="s2id_autogen1"
                                            tabindex="1010"></div>
                                    <select size="4"
                                            name="ctl00$ctl00$MainContent$SearchContent$cSearch$Srh_card_id$ddlList"
                                            id="MainContent_SearchContent_cSearch_Srh_card_id_ddlList" tabindex="-1"
                                            class="validate[custom[htmlTag]]  noRequiredDdl Search select2-offscreen"
                                            title="">
                                        <option <?php echo empty($cardNumber) ? 'selected=""' : ''; ?>
                                            value="">
                                        </option>
                                        <option <?php echo empty($cardNumber) ? '' : 'selected="selected"'; ?>
                                            value="0000001234567890">************7890
                                        </option>

                                    </select></td>
                                <td class="Control_Item" style="white-space:nowrap;">
                                </td>
                            </tr>
                            <tr>
                                <td class="Control_Caption">照会期間</td>
                                <td class="Control_Item" style="white-space:nowrap;">
                                    <div class="input-between-panel">
                                        <div id="datetimepicker" class="input-append date">
                                            <input
                                                name="ctl00$ctl00$MainContent$SearchContent$cSearch$Srh_transaction_datetime$TextBox"
                                                type="text"
                                                value="<?php echo empty($fromDate) ? (new DateTime())->modify('-1 month')->format('Y/m/d') : $fromDate ?>"
                                                maxlength="10" size="11"
                                                id="MainContent_SearchContent_cSearch_Srh_transaction_datetime_TextBox"
                                                tabindex="1010"
                                                class="validate[custom[htmlTag],maxSize[10],custom[date],future[1900/01/01],past[2500/12/31]]  datepicker Search"
                                                text-type="Varchar" data-format="yyyy/MM/dd" placeholder="yyyy/MM/dd"
                                                style="text-align:left;padding-left:2px;"><span class="add-on">
			<i data-time-icon="icon-time" data-date-icon="icon-calendar">
            </i>
			</span>
                                        </div>
                                        <span class="input-between-label">～</span>
                                        <div id="datetimepicker" class="input-append date">
                                            <input
                                                name="ctl00$ctl00$MainContent$SearchContent$cSearch$Srh_transaction_datetime2$TextBox"
                                                type="text"
                                                value="<?php echo empty($toDate) ? date('Y/m/d') : $toDate ?>"
                                                maxlength="10" size="11"
                                                id="MainContent_SearchContent_cSearch_Srh_transaction_datetime2_TextBox"
                                                tabindex="1010"
                                                class="validate[custom[htmlTag],maxSize[10],custom[date],future[1900/01/01],past[2500/12/31]]  datepicker Search"
                                                text-type="Varchar" data-format="yyyy/MM/dd" placeholder="yyyy/MM/dd"
                                                style="text-align:left;padding-left:2px;"><span class="add-on">
			<i data-time-icon="icon-time" data-date-icon="icon-calendar">
            </i>
			</span>
                                        </div>

                                    </div>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="Search_Area_Button">
                        <input type="submit" name="ctl00$ctl00$MainContent$SearchContent$BtnClear" value="クリア"
                               onclick="" id="MainContent_SearchContent_BtnClear" accesskey="C"
                               tabindex="-1" class="Button button button-rounded button-flat cancelValidate  Clear"
                               onkeydown="">
                        &nbsp;
                        <input type="submit" name="ctl00$ctl00$MainContent$SearchContent$BtnSearch" value="検索"
                               onclick=""
                               id="MainContent_SearchContent_BtnSearch" tabindex="-1"
                               class="Button button button-rounded button-flat-primary  Search"
                               onkeydown="">
                    </div>
                </fieldset>
            </div>

            <?php
            if ($fromDate && $toDate) {
                echo $this->render('_cedyna_trading_history_list', [
                    'tradingHistories' => $tradingHistories,
                ]);
            }
            ?>

        </div>
        <div class="panel-buttons">
            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-spacing: 0px;">
                <tbody>
                <tr>
                    <td align="left" width="180px" style="vertical-align: top;">
                        <input type="submit" name="ctl00$ctl00$MainContent$BtnBackPage" value="戻る"
                               id="MainContent_BtnBackPage" tabindex="-1"
                               class="Button button button-rounded button-flat cancelValidate  Back"
                               onkeydown=""
                               style="margin-bottom: 3px;">
                    </td>
                    <td align="right" style="vertical-align: top;"></td>
                </tr>
                </tbody>
            </table>
        </div>
    </div><!-- /.container -->
</form>
