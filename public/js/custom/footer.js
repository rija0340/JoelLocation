

$(function () {

    window.addEventListener("load", reportWindowSize);

    window.addEventListener('resize', reportWindowSize);


    function reportWindowSize() {
        var elem = document.querySelector(".footer-bottom .d-flex");
        var testClass = elem.classList.contains('flex-column');
        var signature = document.querySelector(".signature");
        var testClassSign = signature.classList.contains("mr-auto");

        if (window.innerWidth < 1200) { //inferieur a 1200
            if (testClass) {

            } else {
                elem.classList.add('flex-column');
                signature.classList.remove('mr-auto');

            }

        } else { //superieur a 1200

            if (testClass) {
                elem.classList.remove('flex-column');
                signature.classList.add('mr-auto');
            } else {

            }

        }

        // elem.classList.add('flex-column');
        // elem.classList.remove('flex-column');
        // console.log(" la réponse est " + test);
        // console.log(window.innerHeight);
        // console.log(window.innerWidth);
    }
});