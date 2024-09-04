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

});
