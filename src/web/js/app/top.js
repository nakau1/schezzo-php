$(function () {
    var balanceTextDom = $(".balance_text");
    var balanceLoadingImageDom = $(".balance_loading_image");
    var balancePriceDom = $(".balance_price span");

    var activated = ($("input[name='activated']").val() == 1);

    var colorMap = [
        ['#e60012', 20],
        ['#f39800', 40],
        ['#fdd000', 60],
        ['#f7ea76', 80],
        ['#b8d447', 100]
    ];

    var donuts = new DonutsCanvas();
    donuts.canvas = document.querySelector("#canvas");
    donuts.emptyColor = "#E5E5E5";
    donuts.applyColor = function () {
        var res = '#000';
        var ratio = this.ratio;
        for (var i in colorMap) {
            var v = colorMap[i];
            if (ratio <= v[1] / 100.0) {
                return v[0];
            }
        }
        return res;
    };
    donuts.ratio = 0;
    donuts.draw();

    // 残高取得(AJAX)
    $.ajax({
        type: "GET",
        url: "/ajax/get-values",
        dataType: "json",
        data: {"access-token": $('#user-token').val()},
        success: function (data) {
            var price = parseInt(data["price"]);
            var percentage = parseFloat(data["percentage"]);
            var animationTimer;

            // http://gizma.com/easing/#quint1
            function easeInOutQuad(t, b, c, d) {
                t /= d / 2;
                if (t < 1) return c / 2 * t * t + b;
                t--;
                return -c / 2 * (t * (t - 2) - 1) + b;
            }

            // アニメーション用関数
            function render(step, maxStep) {
                var midStep = Math.floor(maxStep * 0.2); // 係数を減らすほど、最初の満タンまでの速度が早くなる
                if (step < midStep) {
                    // step1: 0% -> 100% (Increase)
                    donuts.ratio = easeInOutQuad(step, 0, 1, midStep);
                } else {
                    // step2: 100% -> ratio (Decrease)
                    donuts.ratio = easeInOutQuad(step - midStep, 1, percentage - 1, maxStep - midStep);
                }

                donuts.draw();
                if (step >= maxStep) {
                    // finish animation
                    return;
                }
                // continue animation
                animationTimer = requestAnimationFrame(render.bind(null, step + 1, maxStep));
            }

            if (activated) {
                render(0, 360); // 引数maxStepを増やすほどアニメーションの時間が長くなる
            }

            // 文字表示
            balancePriceDom.text(price.toLocaleString());
            balanceLoadingImageDom.hide();
            balanceTextDom.show();
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            if (errorThrown == "Unauthorized") {
                location.href = "auth/sign-out";
            } else {
                balancePriceDom.text("-- ");
                balanceLoadingImageDom.hide();
                balanceTextDom.show();
            }

            donuts.ratio = 0;
            donuts.draw();
        }
    }).done(function (data) {
        if (!data.loggedIn) {
            location.href = data.url;
        }
    });

    // ポイントサイト一覧
    $(".site_rogo_img").imgLiquid({
        fill: false
    });
    $(".multiple-items").slick({
        infinite: true,
        slidesToShow: 3,
        slidesToScroll: 1
    });
});