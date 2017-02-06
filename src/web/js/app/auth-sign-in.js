$(function ()
{
    var loginFormDom       = $("#login-form");
    var loginFormSubmitDom = $(".login-form-submit");
    var cedynaIdDom        = $("#cedyna-id");
    var cedynaIdPieceDom   = $(".cedyna-id-piece");

    loginFormSubmitDom.on('click', function () {
        loginFormDom.submit();
    });

    loginFormDom.on('submit', function () {
        var cedynaId = '';
        cedynaIdPieceDom.each(function () {
            cedynaId += $(this).val();
        });
        cedynaIdDom.val(cedynaId);
    });

    cedynaIdPieceDom.on('keyup', function () {
        // 4桁入力したら次のフォームにフォーカスする
        if ($(this).val().length === 4) {
            $(this).next().focus();
        }
    });
});
