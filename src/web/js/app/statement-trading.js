$(function () {
    var tradingListDom = $("#trading_list");
    var monthArgumentDom = $("input[name='month']");

    $.ajax({
        type: "GET",
        url: "/ajax/trading-list",
        dataType: "html",
        data: {
            month: monthArgumentDom.val(),
            "access-token": $('#user-token').val()
        },
        success: function (data) {
            if ($.trim(data) == 'Unauthorized') {
                location.href = "auth/sign-out?fail=1";
                return;
            }
            tradingListDom.html(data);
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            if (errorThrown == "Unauthorized") {
                location.href = "auth/sign-out";
            } else {
                console.log(XMLHttpRequest.responseText);
                location.href = "/js-error";
            }
        }
    });
});
