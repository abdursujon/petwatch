function getLocation(lat, long){
    var out = document.getElementById("output");
    out.innerHTML = "Latitude is: " + lat + ", Longitude is " + long;
}

function success(position){
    getLocation(position.coords.latitude, position.coords.longitude);
}

function error(){
    alert('ERROR(' + error.code + '):' + error.message);
}

var status = document.getElementById('status');

if (!navigator.geolocation) {
    status.textContent = 'Geolocation is not supported by your browser';
} else {
    status.textContent = 'Locating...'
    navigator.geolocation.getCurrentPosition(success, error);
}
