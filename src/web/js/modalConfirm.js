/************************************************************
 * Confirm Modal Plugin V1.0
 * https://github.com/klutche/confirm_modal
 * Released under the MIT license
 ************************************************************/

$(function () {
    // 何度も表示されないようにDOMの存在確認をする
    if (document.getElementsByClassName("modal_box").length <= 0) {
        return;
    }

    var modal = $(".modal_box");
    var opacity = 0.5;
    var button = $(".close_modal");
    var limit = 0;//Cookieの有効期限(分)
    var cookie = $.cookie("modal");
    if (cookie !== "off") {
        var height = $(window).height();
        var overlay = $("<div class=" + 'overlay_box' + "></div>");
        overlay.css({
            "position": "fixed",
            "z-index": 997,
            "top": 0,
            "left": 0,
            "height": height + "%",
            "width": 100 + "%",
            "background": "#000",
            "opacity": opacity
        });
        $("body").append(overlay).css("height", height);
        modal.css("display", "block");
    }
    button.click(function () {
        $(overlay).fadeOut("slow");
        $(modal).hide();
        var clearTime = new Date();
        clearTime.setTime(clearTime.getTime() + (limit * 60 * 1000));
        $.cookie("modal", "off", {expires: clearTime, path: "/"});
    });
    $(".remove_cookie").click(function () {
        $.removeCookie("modal", {expires: -1, path: "/"});
        location.reload();
    });

    //オーバーレイ背景clickでモーダルを閉じる
    $(".overlay_box").click(function () {
        $(overlay).fadeOut("slow");
        $(modal).hide();
        var clearTime = new Date();
        clearTime.setTime(clearTime.getTime() + (limit * 60 * 1000));
        $.cookie("modal", "off", {expires: clearTime, path: "/"});
    });
});

