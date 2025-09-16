function flashy(message, link) {
    var template = $($('#flashy-template').html())
    $('.flashy').remove()
    template
        .find('.flashy__body')
        .html(message)
        .attr('href', link || '#')
        .end()
        .appendTo('.content-header')
        .hide()
        .fadeIn(300)
        .delay(3000)
        .animate(
            {
                marginRight: '-200%',
            },
            300,
            'swing',
            function () {
                $(this).remove()
            }
        )
}
