
function populateSelectNew(options, idElem) {

    var select = document.getElementById(idElem);
    $("#" + idElem).empty(); //remove old options jquery

    for (var i = 0; i < options.length; i++) {
        var opt = options[i];
        var el = document.createElement("option");
        el.text = opt.text;
        el.value = opt.id;
        select.add(el);

    }
}

function populateSelectEdit(options, idElem, idSelected) {

    var select = document.getElementById(idElem);
    var selected = document.getElementById(idSelected).value;
    $("#" + idElem).empty(); //remove old options jquery

    for (var i = 0; i < options.length; i++) {

        var opt = options[i];
        var option = document.createElement("option");
        option.text = opt.text;
        option.value = opt.id;
        if (selected == option.text) {
            option.setAttribute('selected', 'selected');
        }
        select.add(option);

    }
}
