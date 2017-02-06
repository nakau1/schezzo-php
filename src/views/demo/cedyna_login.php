<?php
/* @var $this yii\web\View */

// url: Contents/MBR/MemberLogin.aspx
$this->title = 'ログイン - マイページ';
?>

<form method="post" action="" onkeypress="" id="formMain">
    <div class="container">
        <!-- system logo -->
        <div class="content-logo">
        </div>
        <!-- content of Notice -->
        <div class="panel panel-success content-info">
            <div class="panel-heading panel-toggle panel-caption">お知らせ</div>
            <div class="panel-body">
                <ul>
                    <li>
                        <div class="notice_content_title">
                            <span></span>&nbsp;<a class="notice_content_title_link"><span
                                    class="content_title content-title-notice">会員番号、パスワードについて</span></a>
                        </div>
                        <div class="notice_content_contents alert alert-success" style="">
                            <span>会員番号はお届けのカード裏面の右下、もしくはカードをお送りした際の貼付台紙に記載の16桁の番号になります。<br>パスワードは、お申込みの際にご登録いただいた、英数混在の8-12桁の値になります。</span>
                            <div class="notice_content_contents_link"></div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
        <!-- content of Login -->
        <div class="panel panel-info content-login">
            <div class="panel-heading panel-caption">ログイン</div>
            <div class="panel-body">
                <div class="guide">会員番号とパスワードを入力し、ログインボタンをクリックしてください。</div>
                <div>
                    <div class="form-group">
                        <div class="input-group">
                            <span class="input-group-addon">会員番号</span>
                            <input name="TbxMemberNumber" type="text" id="TbxMemberNumber"
                                   class="form-control input-lg validate[required,custom[htmlTag],custom[onlyLetterNumber]] Reauired"
                                   placeholder="会員番号を入力してください。" inputmode="verbatim">
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="input-group">
                            <span class="input-group-addon">パスワード</span>
                            <input name="TbxPassword" type="password" id="TbxPassword"
                                   class="form-control input-lg validate[required,custom[htmlTag]] Reauired"
                                   placeholder="パスワードを入力してください。" autocomplete="off">
                        </div>
                    </div>
                    <div class="form-group" style="text-align: center;">
                        <input type="submit" name="BtnLogin" value="ログイン" id="BtnLogin"
                               class="button button-rounded button-flat-primary button-large">
                    </div>
                </div>
                <div class="contents-login-guide">
                    <div class="login-guide"><i class="fa fa-life-ring" aria-hidden="true"></i>パスワードを一定回数連続で間違えるとログインできません。セディナアンサーセンターまでご問い合わせください。
                    </div>
                    <div class="login-guide"><i class="fa fa-life-ring" aria-hidden="true"></i><a
                            id="link_password_reset_request" href="">パスワードをお忘れの方はこちらから</a>
                    </div>
                    <div class="login-guide-supportdesk alert alert-info" role="alert">
                        <div class="login-guide-supportdesk supportdesk-name">セディナアンサーセンター</div>
                        <div class="login-guide-supportdesk"><i class="fa fa-phone" aria-hidden="true"></i>：03-4330-1377
                        </div>
                        <div class="login-guide-supportdesk">受付時間：9:30-17:00 1月1日休</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>