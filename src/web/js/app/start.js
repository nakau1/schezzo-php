$(document).ready(function () {
    $(".modal_box").css({top: "0px", opacity: "0"});
    setTimeout(function () {
        $(".modal_box").stop().animate({top: "25", opacity: "1"}, 400);
    }, 1000);
});

$(function () {
    $('.modal_box_inner').slick({
        infinite: false,
        arrows: false,
    });
    //画像送り
    $('.tutorial_00').click(function () {
        $('.slick-track').css({
            '-webkit-transform': 'translate3d(-340px,0,0)',
            '-webkit-transition': '-webkit-transform 400ms'
        });
    });
    $('.tutorial_01').click(function () {
        $('.slick-track').css({
            '-webkit-transform': 'translate3d(-680px,0,0)',
            '-webkit-transition': '-webkit-transform 400ms'
        });
    });
    $('.tutorial_02').click(function () {
        $('.slick-track').css({
            '-webkit-transform': 'translate3d(-1020px,0,0)',
            '-webkit-transition': '-webkit-transform 400ms'
        });
    });
    $('.tutorial_03').click(function () {
        $('.slick-track').css({
            '-webkit-transform': 'translate3d(-1360px,0,0)',
            '-webkit-transition': '-webkit-transform 400ms'
        });
    });
});