$(function () {
    $('.chart').easyPieChart({
        barColor: '#000',
        trackColor: '#949494',
        scaleColor: false,
        scaleLength: 0,
        lineCap: 'square',
        lineWidth: 15,
        // percent: 75,
        trackWidth: undefined,
        size: 100,
        rotate: 0,
        animate: {
            duration: 1000,
            enabled: true
        },
        onStep: function (from, to, percent) {
            $(this.el).find('.percent').text(Math.round(percent));
        }
    });
});