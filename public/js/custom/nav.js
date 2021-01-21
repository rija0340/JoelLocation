


// if (window.matchMedia("(max-width: 700px)").matches) { // If media query matches

//     document.body.style.backgroundColor = "yellow";
//     $("test").insertAfter(".slicknav_brand");

// } else {
//     document.body.style.backgroundColor = "pink";
// }

$(window).ready(function () {

    if (320 <= $(window).width() <= 767) {

        console.log("test");
        $(".header-social-tablet").removeClass("hidden")
        $(".header-social-tablet").insertAfter(".slicknav_brand");

    } else {
        $(".header-social-tablet").addClass("hidden")


    }

});