/**
 * send, open, readyState, status, responseText all built method and properties of XMLHttpRequest
 * @type {XMLHttpRequest}
 */
var xhr = new XMLHttpRequest();
xhr.open('GET', 'send-ajax-data.php'); // retrieve data

xhr.onreadystatechange = function () {
    var DONE = 4;
    var OK = 200;
    if (xhr.readyState == DONE) {
        if (xhr.status == OK) {
            document.getElementById('ajaxBtn').addEventListener('click', function () {
                document.getElementById('ajax_response').textContent = xhr.responseText; // read the response by responseText
            });
        } else {
            console.log('Error: ' + xhr.status);
        }
    }
}

xhr.send(); // send this request to the server

/**
 * response.text(), data, response, error all fetch() method and properties.
 * fetch() method
 */
fetch('send-ajax-data.php').then(function (response) {
    if (!response.ok) {
        throw new Error('HTTP ' + response.status);
    }
    return response.text();
}).then(function (data) {
    document.getElementById('fetchBtn').addEventListener('click', function () {
        document.getElementById('fetch_response').textContent = data;
    });
}).catch(function (error) {
    document.getElementById('fetch_response').innerText = error;
});

// Live search
function showHint(str) {
    if (str.length == 0) {
        document.getElementById('hintOutput').innerHTML = "";
        return;
    } else {
        var xmlhttp = new XMLHttpRequest();

        xmlhttp.onreadystatechange = function () {
            if (this.readyState == 4 && this.status == 200) {
                var uic = document.getElementById("hintOutput");
                uic.innerHTML = this.responseText;
            }
        };

        xmlhttp.open("GET", "hint.php?q=" + str, true);
        xmlhttp.send();
    }
}

// Live search example two
function showHintAsAnOption(str) {
    if (str.length == 0) {
        document.getElementById('resultSelectionBox').innerHTML = "";
        return;
    } else {
        var xmlRequest = new XMLHttpRequest();
        xmlRequest.onreadystatechange = function () {
            if (this.readyState == 4 && this.status == 200) {
                var uic = document.getElementById("resultSelectionBox");
                uic.innerHTML = "";
                var names = this.responseText.split(',');
                for (var i = 0; i < names.length; i++) {
                    var opt = document.createElement('option');
                    opt.value = names[i];
                    opt.innerHTML = names[i];
                    uic.appendChild(opt);
                }
                uic.size = names.length; // auto expand of the option based on user input
            }
        }

        xmlRequest.open("GET", "hint.php?q=" + str, true);
        xmlRequest.send();
    }
}

function showJsonHintOutput(str) {
    if (str.length == 0) {
        document.getElementById('jsonOutput').innerHTML = "";
        return;
    } else {
        var xhrTwo = new XMLHttpRequest();
        xhrTwo.onreadystatechange = function () {
            if (this.readyState == 4 && this.status == 200) {
                var jsondata = JSON.parse(this.responseText);
                var uic = document.getElementById("jsonOutput");
                uic.innerHTML = "";
                var people = jsondata.people;
                for (var i = 0; i < people.length; i++) {
                    if (people[i].name.toLowerCase().indexOf(str.toLowerCase()) >= 0) {
                        uic.innerHTML += "<p>" + people[i].name + " - " + people[i].craft + "</p>";
                    }
                }
            }
        }
        xhrTwo.open('GET', 'people-in-space.json?q=' + str, true);
        xhrTwo.send();
    }
}

function showJsonHintOutputTwo(str) {
    if (str.length == 0) {
        document.getElementById('jsonOutputTwo').innerHTML = "";
        return;
    } else {
        var xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function () {
            if (this.readyState == 4 && this.status == 200) {
                var jsondataTwo = JSON.parse(this.responseText);
                var uic = document.getElementById("jsonOutputTwo");
                uic.innerHTML = "";
                var manufacturer = jsondataTwo.cardata;
                for (var i = 0; i < manufacturer.length; i++) {
                    if (manufacturer[i].manufacturer.toLowerCase().indexOf(str.toLowerCase()) >= 0) {
                        uic.innerHTML += "<ol>" + manufacturer[i].manufacturer + " "
                            + manufacturer[i].model + " $" + manufacturer[i].price + " " + "<a href='" +
                            manufacturer[i].wiki + "'>" + manufacturer[i].manufacturer + "</a>" + "</ol>";
                    }
                }
            }
        }
        xhr.open('GET', 'car.json?q=' + str, true);
        xhr.send();
    }
}