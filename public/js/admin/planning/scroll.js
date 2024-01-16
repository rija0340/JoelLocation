
$(function () {
    $(".wmd-view-topscroll").scroll(function () {
        $(".gantt_hor_scroll").scrollLeft($(this).scrollLeft());
    });
    $(".gantt_hor_scroll").scroll(function () {
        $(".wmd-view-topscroll")
            .scrollLeft($(this).scrollLeft());
    });
});