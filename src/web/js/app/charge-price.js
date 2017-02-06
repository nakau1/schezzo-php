$(function () {

    // 「チャージする」を押下した後に、ユーザ操作でモーダルを閉じられないように制御するためのフラグ
    var inChargeProcessing = false;

    //  fancybox - jquery plugin
    //  http://fancybox.net/
    //  http://fancyapps.com/fancybox/
    $(".fancybox").fancybox({
        prevEffect: 'none',
        nextEffect: 'none',
        closeBtn:   false,
        arrows:     false,
        nextClick:  false,
        beforeClose: function () {
            return !inChargeProcessing;
        },
        afterClose: function () {
            validatePriceInput();
        }
    });

    // DOMs
    var cardIssueFeeDom          = $("input[name='card-issue-fee']");
    var chargeSourceCodeDom      = $("input[name='charge-source-code']");
    var priceInputFormDom        = $("#charge-price");
    var priceInputDom            = $("#chargepriceform-price");
    var resetPriceButtonDom      = $("#reset-price");
    var pointTotalPriceDom       = $("#point-total-price");
    var add100ButtonDom          = $("#add-100");
    var add1000ButtonDom         = $("#add-1000");
    var add5000ButtonDom         = $("#add-5000");
    var confirmChargeButtonDom   = $("#confirm-charge-button");
    var confirmChargeDisabledDom = $("#confirm-charge-disabled   ");
    var commitChargeButtonDom    = $("#commit-charge-button");
    var commitChargeLoadingDom   = $("#commit-charge-loading");
    var confirmChargePriceDom    = $("#confirm-charge-price");
    var confirmBonusPriceDom     = $("#confirm-bonus-price");
    var confirmTotalPriceDom     = $("#confirm-total-price");

    // should not work submit
    priceInputFormDom.submit(function () {
        return false;
    });

    setCommitChargeButtonEnabled(true);
    setConfirmChargeButtonEnabled(false);

    /**
     * 入力された値が空かどうか
     * @returns {boolean}
     */
    function isEmptyPriceInput() {
        return (priceInputDom.val().length <= 0);
    }

    /**
     * 入力された値が整数かどうか
     * @returns {boolean}
     */
    function isNumberPriceInput() {
        return priceInputDom.val().match(/^([1-9]\d*|0)$/);
    }

    /**
     * 入力された値を整数で取得する
     * @returns {number}
     */
    function getInputtedPrice() {
        var inputted = 0;
        if (!isEmptyPriceInput() || isNumberPriceInput()) {
            inputted = parseInt(priceInputDom.val());
        }
        return inputted;
    }

    /**
     * ポイント交換総額を計算して表示を更新する
     */
    function changePointTotalPrice() {
        if (isEmptyPriceInput() || !isNumberPriceInput()) {
            pointTotalPriceDom.text("--");
            return;
        }

        var inputted = getInputtedPrice();
        var cardIssueFee = parseInt(cardIssueFeeDom.val());
        var pointTotalPrice = inputted + cardIssueFee;
        pointTotalPriceDom.text((pointTotalPrice).toLocaleString());
    }

    /**
     * 入力フォームのクライアントバリデーションを走らせる
     * @returns {boolean}
     * @see http://stackoverflow.com/questions/28610439/trigger-active-form-validation-manualy-before-submit
     */
    function validatePriceInput() {
        var d = priceInputFormDom.data("yiiActiveForm");
        $.each(d.attributes, function () {
            this.status = 3;
        });
        priceInputFormDom.yiiActiveForm("validate");

        var ret = priceInputFormDom.find(".has-error").length <= 0;
        setConfirmChargeButtonEnabled(ret);
        return ret;
    }

    /**
     * 値に指定の金額を追加する
     * @param price 追加する金額
     */
    function addInputPrice(price) {
        priceInputDom.val(getInputtedPrice() + price);
        changePointTotalPrice();
        validatePriceInput();
    }

    /**
     * 確認ボタンの使用可能/不可を切り替える
     * @param enable 使用可能/不可
     */
    function setConfirmChargeButtonEnabled(enable) {
        toggleDisplayDoms(enable, confirmChargeButtonDom, confirmChargeDisabledDom);
    }

    /**
     * チャージするボタンの使用可能/不可を切り替える(不可時はローディングがボタン内に出る)
     * @param enable 使用可能/不可
     */
    function setCommitChargeButtonEnabled(enable) {
        toggleDisplayDoms(enable, commitChargeButtonDom, commitChargeLoadingDom);
    }

    /**
     * 指定したDOMの使用可能/不可の表示切り替えをする
     * @param enable 使用可能/不可
     * @param showThenEnabled 使用可能時に表示するDOM
     * @param showThenDisabled 使用不可時に表示するDOM
     */
    function toggleDisplayDoms(enable, showThenEnabled, showThenDisabled) {
        if (enable) {
            showThenEnabled.css('display', 'block');
            showThenDisabled.css('display', 'none');
        } else {
            showThenDisabled.css('display', 'block');
            showThenEnabled.css('display', 'none');
        }
    }

    /**
     * 例外ページを表示させる
     */
    function raiseException() {
        window.location.href = '/js-error';
    }

    // +100円ボタン押下時
    add100ButtonDom.click(function () {
        addInputPrice(100);
    });

    // +1000円ボタン押下時
    add1000ButtonDom.click(function () {
        addInputPrice(1000);
    });

    // +5000円ボタン押下時
    add5000ButtonDom.click(function () {
        addInputPrice(5000);
    });

    // リセットボタン押下時
    resetPriceButtonDom.click(function () {
        priceInputDom.val(0);
        changePointTotalPrice();
        validatePriceInput();
    });

    // 入力内容変更時
    priceInputDom.change(function () {
        changePointTotalPrice();
        validatePriceInput();
    });

    // 確認ボタン押下時
    confirmChargeButtonDom.click(function () {
        if (!validatePriceInput()) {
            return false;
        }

        setConfirmChargeButtonEnabled(false);

        var inputtedPrice = getInputtedPrice();
        var cardIssueFee = parseInt(cardIssueFeeDom.val());
        var totalPrice = inputtedPrice + cardIssueFee;

        var params = {'price': totalPrice};

        $.ajax({
            type: "POST",
            url: "bonus-price",
            data: params,
            error: function (request) {
                console.log(request.responseText);
                raiseException();
            },
            success: function (data) {
                var bonusPrice = parseInt(data);

                confirmTotalPriceDom.text(totalPrice.toLocaleString());
                confirmBonusPriceDom.text(bonusPrice.toLocaleString());
                confirmChargePriceDom.text((inputtedPrice + bonusPrice).toLocaleString());

                setCommitChargeButtonEnabled(true);
                $("#confirm-trigger").click();
            }
        });
    });

    // チャージするボタン(確定ボタン)押下時
    commitChargeButtonDom.click(function () {
        setCommitChargeButtonEnabled(false);
        inChargeProcessing = true;

        var params = {
            'charge_source_code': chargeSourceCodeDom.val(),
            'price':              getInputtedPrice()
        };

        $.ajax({
            type: "POST",
            url: "price-request",
            data: params,
            error: function (request) {
                console.log(request.responseText);
                raiseException();
            },
            success: function () {
                window.location.href = 'price-finished';
            }
        });
    });

    // ポイント交換総額の計算
    changePointTotalPrice();
});
