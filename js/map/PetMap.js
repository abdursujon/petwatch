export class PetMap {
    constructor(elementId, lat, long, zoom) {
        this.map = L.map(elementId).setView([lat, long], zoom);
        this.popupOption = {"closeButton": false};
        this.markers = []; // stores all marker objects on the map
        this.allData = []; // stores all pet data from ajax
        this.sightingMode = false // tracks if user on sighting mode to add a new sighting
        this.initialTileLayer();
    }

    initialTileLayer() {
        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OPenStreetMap</a> contributors'
        }).addTo(this.map);
    }

    /**
     * Ajax 1 endpoint 1 (GET)
     * Fetch all data once, then we only render what is on the view radius of the user.
     * Avoid loading alls hundreds of pets marker on the map which maps the map really slow.
     */
    loadMarkers() {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'js/map/SightingsJsonData.php', true);
        xhr.send();
        xhr.onreadystatechange = () => {
            if (xhr.readyState === 4 && xhr.status === 200) {
                this.allData = JSON.parse(xhr.responseText);
                this.renderVisibleMarkers();

                // Re-render markers on the map when user pans or zooms in and out on the map
                this.map.on('moveend', () => {
                    this.renderVisibleMarkers();
                });
            }
        };
    }

    /**
     * Renders markers for only within visible area of the map.
     * This method helps us avoid loading hundreds of pets markers at once on the map which slows the map rendering by a lot.
     * Popup shows pet info for all users.
     * If user is logged in, show "Add A New Sightings" button otherwise show "Log in to add a sighting".
     */
    renderVisibleMarkers() {
        // Clear old markers from the map
        this.markers.forEach(marker => this.map.removeLayer(marker));
        this.markers = [];

        // Only render markers within the current map view
        let bounds = this.map.getBounds();
        this.allData.forEach((pets) => {
            // Only add marker if it's within the visible area
            if (!bounds.contains([pets.latitude, pets.longitude])) {
                return;
            }

            // Build popup HTML card, create a new sightings button only shows user is logged in.
            let markerText = `
            <div id="pet-marker-fuck-off">
               <div class="pet-marker mb-4 mt-4">
                    <div><img src="${pets.photo_url}" alt="${pets.name}"/></div>
                    <p class="pet-name">${pets.name}</p>
                    <p class="pet-location">Last seen: <span class="pet-address">Loading...</span></p>
                    <p class="pet-status">Status: ${pets.status}</p>
                    <p class="pet-sighting">Previous Sighting: ${pets.comment}</p>
                    
                    ${isLoggedIn ? `
                    <input type="hidden" name="pet-id" value="${pets.id}"/>
                    <button type="submit" class="btn btn-primary add-sighting-btn"> Add A New Sighting </button>
                    ` : '<p class="text-muted">Log in to add a sighting</p>'}
                </div>
</div>
             
            `;

            let marker = L.marker([pets.latitude, pets.longitude])
                .addTo(this.map)
                .bindPopup(markerText, {autoClose: true, closeOnClick: true, autoPan:false, autoPanPaddingTopLeft: [0, 100], autoPanPaddingBottomRight:
            [0, 20]})
                // prevent page reload using dom
                .on('mouseover', event => {
                    event.target.openPopup();

                    let markerPoint = this.map.latLngToContainerPoint(event.target.getLatLng());
                    let mapHeight = this.map.getContainer().clientHeight;
                    let popupEl = event.target.getPopup().getElement();

                    if (markerPoint.y < 250) {
                        popupEl.style.transform += ' translateY(250px)';
                    } else if (markerPoint.y > mapHeight - 150) {
                        popupEl.style.transform += ' translateY(-50px)';
                    }
                });

            // Resolve lat/long to readable adress
            this.onPopupShowAddress(marker, pets.latitude, pets.longitude);

            if (isLoggedIn) {
                this.onPopupSightingButtonClick(marker);
            }
            this.markers.push(marker);
        });
    }

    /**
     * reverseGeocodeToHumanReadableLocation() method turns location latitude and longitude to human readable location.
     */
    async reverseGeocodeToHumanReadableLocation(lat, long) {
        try {
            let response = await fetch(
                `https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${long}&format=json&addressdetails=1`
            );
            let data = await response.json();
            let address = data.address;
            let parts = [];
            // Road or neighbourhood to readable location
            if (address.road) parts.push(address.road);
            else if (address.neighbourhood) parts.push(address.neighbourhood);
            // City/town/village
            if (address.city) parts.push(address.city);
            else if (address.town) parts.push(address.town);
            else if (address.village) parts.push(address.village);
            // Postcode
            if (address.postcode) parts.push(address.postcode);
            return parts.join(', ') || data.display_name;
        } catch {
            return `${lat}, ${long}`;
        }
    }

    /**
     * If a popup is open converts lat/long to human readable address
     */
    onPopupShowAddress(marker, lat, long) {
        marker.on('popupopen', () => {
            let addressSpan = marker.getPopup().getElement().querySelector('.pet-address');
            //Only convert it to human readable address when it does not already exist
            if (addressSpan && addressSpan.textContent === 'Loading...') {
                this.reverseGeocodeToHumanReadableLocation(lat, long).then(address => {
                    addressSpan.textContent = address;
                })
            }
        })
    }


    /**
     * When "Add A New Sighting" button is clicked inside a popup, enters create sighting mode popup.
     * Only logged in user can perform this action.
     */
    onPopupSightingButtonClick(marker) {
        marker.on('popupopen', () => {
            let btn = marker.getPopup().getElement().querySelector('.add-sighting-btn');
            if(btn){
                btn.addEventListener('click', () => {
                    let petId = marker.getPopup().getElement().querySelector('input[name="pet-id"]').value;
                    this.enterCreateSightingMode(petId);
                });
            }
        })
    }

    // Ajax endpoint 2
    enterCreateSightingMode(petId){
        this.sightingMode = true;
        this.sightingPetId = petId;
        this.sightingLatLong = null; // Holds the location user click
        this.sightingMarker = null;

        // Close the pet popup so user can interact with the pet map
        this.map.closePopup();

        // Create floating panel UI on top of the map
        let panel = document.createElement('div');
        panel.id = 'sighting-panel';
        panel.innerHTML = `
             <h5>Create New Sighting</h5>
             <p>Click on the map to report pet location.</p>
             <button id="sighting-use-location" class="btn btn-outline-primary btn-sm mb-2 w-100">
             Or choose your current location
             </button>
             <p id="sighting-coords">No location selected</p>
             <input type="text" id="sighting-comment" class="w-100 mb-2" placeholder="Add a comment..." required/>
             <button id="sighting-submit" class="btn btn-primary me-2" disabled>Submit</button>
             <button id="sighting-cancel" class="btn btn-secondary">Cancel</button>
        `;
        this.map.getContainer().style.position = 'relative';
        this.map.getContainer().appendChild(panel);

        // Option 1: Click on the map to pick a location
        this.onMapClick = (e) =>{
            this.setSightingLocation(e.latlng.lat, e.latlng.lng);
        };
        this.map.on('click', this.onMapClick);

        // Option 2: Use GPS current geolocation.
        // Use Geolocation.js

        // Post new sighting to viewSighting.php
        document.getElementById('sighting-submit').addEventListener('click', () => {
            let comment = document.getElementById('sighting-comment').value.trim();
            if(!comment){
                alert('Please enter a comment.');
                return;
            }
            if(!this.sightingLatLong){
                alert('Please select a location');
                return;
            }

            // Send post request to record the sighting
            var sightingXhr = new XMLHttpRequest();
            sightingXhr.open('POST', 'viewSightings.php', true);
            sightingXhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            sightingXhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            sightingXhr.send('pet-id=' + this.sightingPetId
                + '&sighting-comment=' +encodeURIComponent(comment)
                + '&latitude=' + this.sightingLatLong.lat
                + '&longitude=' + this.sightingLatLong.lng
            );

            sightingXhr.onreadystatechange = () =>{
                if(sightingXhr.readyState === 4){
                    if(sightingXhr.status === 200){
                        alert('Sighting added successfully.');
                    } else {
                        alert('Failed to add sighting.')
                    }
                    this.exitSightingMode();
                }
            };

        });

        document.getElementById('sighting-cancel').addEventListener('click', () =>{
           this.exitSightingMode();
        });
    }

    /**
     * Set the sighting location
     */
    setSightingLocation(lat, long){
        this.sightingLatLong = {lat: lat, lng:long};
        let coordsEl = document.getElementById('sighting-coords');
        coordsEl.textContent = `${lat.toFixed(5)}, ${long.toFixed(5)}`;
        this.reverseGeocodeToHumanReadableLocation(lat, long).then(address => {
            coordsEl.textContent = address;
        })

        // Enable submit when location is set
        document.getElementById('sighting-submit').disabled = false;

        // Remove old temporary marker if user re-clicks on a different location on the map
        if(this.sightingMarker){
            this.map.removeLayer(this.sightingMarker);
        }
        this.sightingMarker = L.marker([lat, long]).addTo(this.map);
    }

    /**
     * Exit sighting mode.
     */
    exitSightingMode(){
        this.sightingMode = false;
        this.map.off('click', this.onMapClick);

        // Remove temporary sighting marker from the map
        if(this.sightingMarker){
            this.map.removeLayer(this.sightingMarker);
        }

        // Remove the floating panel from the DOM
        let panel = document.getElementById('sighting-panel');
        if(panel) panel.remove();
    }
}