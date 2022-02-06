// var checkboxesOptionsElements;
// var checkboxesGarantiesElements;
// var btnEnregistrer1;
// var btnEnregistrer2;
// var step3Btn;
// var checkedOptions = false;
// var checkedGaranties = false;
// var compteur1 = 0;
// var compteur2 = 0;


// getElements();
// addEventListener();
// disableBtnEnregistrer();

// function getElements() {
//     // checkboxesOptionsElements = document.querySelectorAll("input[name = 'checkboxOptions[]']");
//     checkboxesGarantiesElements = document.querySelectorAll("input[name = 'checkboxGaranties[]']");
//     btnEnregistrer1 = document.getElementById('btnEnregistrer1');
//     btnEnregistrer2 = document.getElementById('btnEnregistrer2');
//     disableBtnEnregistrer();
// }

// function addEventListener() {
//     // for (let i = 0; i < checkboxesOptionsElements.length; i++) {
//     //     checkboxesOptionsElements[i].addEventListener('click', checkCheckedOptions, false);
//     // }
//     for (let i = 0; i < checkboxesGarantiesElements.length; i++) {
//         checkboxesGarantiesElements[i].addEventListener('click', checkCheckedGaranties, false);
//     }
// }

// // function checkCheckedOptions() {

// //     for (let i = 0; i < checkboxesOptionsElements.length; i++) {
// //         if (checkboxesOptionsElements[i].checked) {
// //             compteur1 = compteur1 + 1;
// //         }
// //     }
// //     if (compteur1 > 0) {
// //         checkedOptions = true;
// //         compteur1 = 0;
// //     } else {
// //         checkedOptions = false;
// //     }
// //     //verifier si au moins une garantie est selectionnée
// //     if (checkedGaranties) {
// //         enableBtnEnregistrer();
// //     } else {
// //         disableBtnEnregistrer();
// //     }
// // }

// function checkCheckedGaranties() {
//     for (let i = 0; i < checkboxesGarantiesElements.length; i++) {
//         if (checkboxesGarantiesElements[i].checked) {
//             compteur2 = compteur2 + 1;
//         }
//     }
//     if (compteur2 > 0) {
//         checkedGaranties = true;
//         compteur2 = 0;
//     } else {
//         checkedGaranties = false;
//     }
//     //verifier si au moins une garantie est selectionnée

//     if (checkedGaranties) {
//         enableBtnEnregistrer();
//     } else {
//         disableBtnEnregistrer();
//     }
// }


// function enableBtnEnregistrer() {

//     btnEnregistrer1.disabled = false;
//     btnEnregistrer2.disabled = false;
// }
// function disableBtnEnregistrer() {
//     btnEnregistrer1.disabled = true;
//     btnEnregistrer2.disabled = true;

// }


$(document).ready(function () {

    //get elements
    $btnSave1 = $("#btnEnregistrer1");
    $btnSave2 = $("#btnEnregistrer2");
    $form = $("#options_garanties");
    var oneChecked = false;

    $("#btnEnregistrer1, #btnEnregistrer2").click(function (e) {
        $("input[name = 'checkboxGaranties[]").each(function () {
            if ($(this).is(':checked')) {
                oneChecked = true;
            }
        });
        //si au moins 1 garantie est selectionné, submit the form
        if (!oneChecked) {
            e.preventDefault();
            $.alert({
                title: 'Erreur',
                icon: 'fa fa-warning',
                type: 'red',
                content: '<p>Veuillez choisir au moins une garantie</p>',
            });
        } else {

        }
    });


});