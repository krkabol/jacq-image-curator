import 'bootstrap';
import '@fortawesome/fontawesome-free/js/all'

import './scss/index.scss';

document.addEventListener("DOMContentLoaded", function (event) {


    // disable button to block multiple resend of request
    if (document.getElementById("importButton")) {
        document.getElementById("importButton").addEventListener("click", function () {
            let button = document.getElementById("importButton");
            button.classList.add("disabled");
            button.onclick = function (event) {
                event.preventDefault();
            }
            document.body.style.cursor = "wait";
        });
    }

    //autorefresh page
    const element = document.getElementById('autorefresh');
    if (element) {
        setInterval(function() {
            location.reload();
        }, 20000); // 20 000 ms = 20 sec
    }

});
