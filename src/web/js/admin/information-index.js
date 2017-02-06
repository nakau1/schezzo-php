$(function () {

    function processCheckboxCommon(dom, requestAction, checkedMessage, uncheckedMessage) {
        var checked = !dom.is(':checked');
        var message = checked ? checkedMessage : uncheckedMessage;

        if (!confirm(message)) {
            return false;
        }

        var params = {
            'id'   : dom.attr('data-id'),
            'value': !checked ? 1 : 0
        };
        console.log(params);
        $.ajax({
            type: "POST",
            url: "information/" + requestAction,
            data: params,
            error: function (request) {
                console.log(request.responseText);
                alert("処理に失敗しました");
            },
            success: function () {}
        });
        return true;
    }

    $('.is_public_check').click(function() {
        return processCheckboxCommon($(this),
            'update-is-public',
            'お知らせを非公開にしますか?',
            'お知らせを公開しますか?'
        );
    });

    $('.is_important_check').click(function() {
        return processCheckboxCommon($(this),
            'update-is-important',
            '重要なお知らせから外しますか?',
            '重要なお知らせに設定しますか?'
        );
    });
});
