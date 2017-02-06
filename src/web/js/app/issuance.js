$(function () {

    $('.fancybox').fancybox({
        prevEffect : 'none',
        nextEffect : 'none',
        closeBtn   : false,
        arrows     : false,
        nextClick  : false,
        padding: 0,
    });

    $('.close_modal').click(function () {
        $.fancybox.close();
    });
});