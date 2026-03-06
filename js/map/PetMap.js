export class PetMap {
    constructor(elementId, lat, lng, zoom, geolocation, ajax) {
        this.map = L.map(elementId).setView([lat, lng], zoom);
        this.popupOption = {"closeButton": false};
        this.markers = []; // stores all marker objects on the map
        this.allData = []; // stores all pet data from ajax
        this.sightingMode = false // tracks if user on sighting mode to add a new sighting
        this.initialTileLayer();
        this.geolocation = geolocation;
        this.ajax = ajax;
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
    setPetDataOnMap(data) {
        this.allData = data;
        this.renderVisibleMarkers();
        this.map.on('moveend', () => {
            this.renderVisibleMarkers();
        });
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
               <div class="pet-marker mb-4 mt-4">
                    <div><img src="${pets.photo_url}" alt="${pets.name}"/></div>
                    <p class="pet-name">${pets.name}</p>
                    <p class="pet-location">Last seen: ${pets.address || 'Unknown location'}</p>
                    <p class="pet-status">Status: ${pets.status}</p>
                    <p class="pet-sighting">Previous Sighting: ${pets.comment}</p>
                    
                    ${isLoggedIn ? `
                    <input type="hidden" name="pet-id" value="${pets.id}"/>
                    <button type="submit" class="btn btn-primary add-sighting-btn"> Add A New Sighting </button>
                    ` : '<p class="text-muted">Log in to add a sighting</p>'}
                </div>
            `;

            let marker = L.marker([pets.latitude, pets.longitude])
                .addTo(this.map)
                .bindPopup(markerText, {
                    autoClose: true,
                    closeOnClick: true,
                    autoPan: false,
                    autoPanPaddingTopLeft: [0, 100],
                    autoPanPaddingBottomRight:
                        [0, 20]
                })
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

            if (isLoggedIn) {
                this.onPopupSightingButtonClick(marker);
            }
            this.markers.push(marker);
        });
    }


    /**
     * When "Add A New Sighting" button is clicked inside a popup, enters create sighting mode popup.
     * Only logged in user can perform this action.
     */
    onPopupSightingButtonClick(marker) {
        marker.on('popupopen', () => {
            let btn = marker.getPopup().getElement().querySelector('.add-sighting-btn');
            if (btn) {
                btn.addEventListener('click', () => {
                    let petId = marker.getPopup().getElement().querySelector('input[name="pet-id"]').value;
                    this.enterCreateSightingMode(petId);
                });
            }
        })
    }

    // Ajax endpoint 2
    enterCreateSightingMode(petId) {
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

        panel.addEventListener('click', (e) => {
            e.stopPropagation();
        });

        // Option 1: Click on the map to pick a location
        this.onMapClick = (e) => {
            this.setSightingLocation(e.latlng.lat, e.latlng.lng);
        };
        this.map.on('click', this.onMapClick);

        // Option 2: Use GPS current geolocation.
        document.getElementById('sighting-use-location').addEventListener('click', () => {
            this.geolocation.locate(
                (lat, lng) => {
                    this.setSightingLocation(lat, lng);
                },
                () => {
                    alert('Could not get your location. Please click on the map instead to choose a location.')
                }
            );
        });

        // Post new sighting to viewSighting.php
        document.getElementById('sighting-submit').addEventListener('click', () => {
            let comment = document.getElementById('sighting-comment').value.trim();
            if (!comment) {
                alert('Please enter a comment.');
                return;
            }
            if (!this.sightingLatLong) {
                alert('Please select a location');
                return;
            }

            // Send post request to record the sighting
            var sightingXhr = new XMLHttpRequest();
            sightingXhr.open('POST', 'createSighting.php', true);
            sightingXhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            sightingXhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

            sightingXhr.onreadystatechange = () => {
                if (sightingXhr.readyState === 4) {
                    if (sightingXhr.status === 200) {
                        try {
                            let result = JSON.parse(sightingXhr.responseText);
                            if (result.success) {
                                alert('Sighting added successfully.');
                                this.ajax.fetchSightings((data) => {
                                    this.setPetDataOnMap(data);
                                }, () => {
                                });
                            } else {
                                alert('Failed to add sighting.')
                            }
                        } catch (e) {
                            alert('Unexpected server response.')
                        }
                    } else {
                        alert('Failed to add sightings.')
                    }
                    this.exitSightingMode();
                }
            };

            sightingXhr.send('pet_id=' + this.sightingPetId
                + '&sighting-comment=' + encodeURIComponent(comment)
                + '&latitude=' + this.sightingLatLong.lat
                + '&longitude=' + this.sightingLatLong.lng
            );
        });

        document.getElementById('sighting-cancel').addEventListener('click', () => {
            this.exitSightingMode();
        });
    }

    /**
     * Set the sighting location
     */
    setSightingLocation(lat, lng) {
        this.sightingLatLong = {lat: lat, lng: lng};
        let coordsEl = document.getElementById('sighting-coords');
        coordsEl.textContent = `${lat.toFixed(5)}, ${lng.toFixed(5)}`;

        this.ajax.reverseLatLngToHumanReadableAddress(lat, lng, (address) => {
                coordsEl.textContent = address
                this.sightingAddress = address;
            },
            () => {
                this.sightingAddress = lat.toFixed(5) + ', ' + lng.toFixed(5);
            }
        );

        // Enable submit when location is set
        document.getElementById('sighting-submit').disabled = false;
        // Remove old temporary marker if user re-clicks on a different location on the map
        if (this.sightingMarker) {
            this.map.removeLayer(this.sightingMarker);
        }
        this.sightingMarker = L.marker([lat, lng]).addTo(this.map);
    }

    /**
     * Exit sighting mode.
     */
    exitSightingMode() {
        this.sightingMode = false;
        this.map.off('click', this.onMapClick);

        // Remove temporary sighting marker from the map
        if (this.sightingMarker) {
            this.map.removeLayer(this.sightingMarker);
        }

        // Remove the floating panel from the DOM
        let panel = document.getElementById('sighting-panel');
        if (panel) panel.remove();
    }
}