var notif;
getElements();

function getElements() {
    notif = document.getElementById('notification_pwd');
}
if (notif) {
    setTimeout(closeAlert, 5000);

} else {

}


function closeAlert() {
    nofif.classList.add('hide');

}