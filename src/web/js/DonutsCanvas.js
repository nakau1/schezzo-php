// requestAnimationFrame polyfill.
// http://www.webcreativepark.net/javascript/animations/requestanimationframe/
window.requestAnimationFrame = window.requestAnimationFrame ||
    window.webkitRequestAnimationFrame ||
    window.mozRequestAnimationFrame ||
    window.msRequestAnimationFrame ||
    function (f) {
        return window.setTimeout(f, 15);
    };

function DonutsCanvas() {
    /// @type {Canvas}
    this.canvas = null;
    /// @type {String}
    this.emptyColor = null;
    ///
    this.applyColor = function () {
        return null
    };

    /// 0 ~ 1.0
    this.ratio = 0.0;

    /// draw
    this.draw = function () {
        if (!this.canvas || !this.canvas.getContext) {
            return false;
        }
        var length = Math.min(canvas.width, canvas.height) / 2;
        var center = {x: canvas.width / 2, y: canvas.height / 2};
        var innerLength = length * 0.7; // inner circle length.
        var ratio = this.ratio;

        var ctx = this.canvas.getContext('2d');
        ctx.clearRect(0, 0, canvas.width, canvas.height);

        var originRadian = -Math.PI / 2;
        var startAngle = originRadian;

        // // fill empty color donut.
        ctx.fillStyle = this.emptyColor;
        ctx.beginPath();
        ctx.arc(center.x, center.y, innerLength, Math.PI, Math.PI * 2, false);
        ctx.arc(center.x, center.y, length, Math.PI * 2, Math.PI, true);
        ctx.closePath();
        ctx.fill();

        ctx.beginPath();
        ctx.arc(center.x, center.y, innerLength, 0, Math.PI, false);
        ctx.arc(center.x, center.y, length, Math.PI, 0, true);
        ctx.closePath();
        ctx.fill();

        // fill ratio color donut arc.
        ctx.fillStyle = (this.applyColor).bind(this)();
        ctx.beginPath();
        var endAngle = Math.PI * 2 * ratio + originRadian;
        ctx.arc(center.x, center.y, innerLength, startAngle, endAngle, false);
        ctx.arc(center.x, center.y, length, endAngle, startAngle, true);

        ctx.closePath();
        ctx.fill();
    }.bind(this);
}