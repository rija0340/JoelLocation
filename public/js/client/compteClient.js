var alert;
getElements();

function getElements() {
    alert = document.getElementById('notification_pwd');
}
if (alert) {
    setTimeout(closeAlert, 5000);

} else {
    alert('tsy misy');

}


function closeAlert() {
    alert.classList.add('hide');

}