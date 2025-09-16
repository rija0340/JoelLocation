var test;
let listItems = document.querySelectorAll('.side-menu li');

$(window).on('load', function () {

    listItems = document.querySelectorAll('.side-menu li');
    listItems.forEach((item, index) => {

        var spanElem = item.lastElementChild.lastElementChild;

        if ($(item).hasClass('current-page')) {
            // alert(index);
            $(spanElem).removeClass('fa-chevron-right').addClass('fa-chevron-down');
        }

        if ($(item).hasClass('active')) {

            $(item.firstElementChild.lastElementChild).removeClass('fa-chevron-right').addClass('fa-chevron-down');

        }

    });


});
