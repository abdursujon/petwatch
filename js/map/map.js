var map = L.map('map').setView([53.4631, -2.2913], 14); //L is leaflet library global object lat, long, and zoom level

/**
 * tileLayer loads the map tile images from a tile server. Maps are made up of small square images (tiles) stitched together
 * {z}/{x}/{y} — These are placeholders that Leaflet replaces automatically:
 *   - {z} = zoom level (0-18). Higher zoom = more detail
 *   - {x} = column number of the tile (horizontal position)
 *   - {y} = row number of the tile (vertical position)
 *   So when we pan/zoom the map, Leaflet requests the right tile images by filling in these values. For example:
 *   https://tile.openstreetmap.org/14/8192/5765.png
 *
 *   &copy; — This is the HTML entity for the copyright symbol (©).
 *   attribution — This is the credit text shown in the bottom corner of the map. OpenStreetMap requires you to give credit to their contributors.
 *   contributors — OpenStreetMap data is created by volunteers (regular people) who map roads, buildings, etc. They are the "contributors."
 */
L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OPenStreetMap</a> contributors'
}).addTo(map);


var xhr = new XMLHttpRequest();
xhr.open('GET','markers.php', true);
xhr.send();

let popupOption = { "closeButton": false}

xhr.onreadystatechange = function(){
    if(xhr.readyState === 4 && xhr.status === 200){
        var data = JSON.parse(xhr.responseText);
        data.forEach(function(pets){
            let markerText = '<p>' + pets.name + '<br/>' + pets.comment + '</p>';
            var marker = L.marker([pets.latitude, pets.longitude]).addTo(map).on('mouseover', event =>{
                event.target.bindPopup(markerText).openPopup();}).on('mouseout', event => {event.target.closePopup();});
        });
    }
};