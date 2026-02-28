export class PetMap {
    constructor(elementId, lat, long, zoom) {
        this.map = L.map(elementId).setView([lat, long], zoom);
        this.popupOption = { "closeButton": false };
        this.markers = []; // stores all marker objects on the map
        this.allData = []; // stores all pet data from ajax
        this.initialTileLayer();
    }

    initialTileLayer() {
        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OPenStreetMap</a> contributors'
        }).addTo(this.map);
    }

    // Fetch all data once, then we only render what is on the view radius of the user. Avoid loading alls hundreds of pets marker on the map which maps the map really slow.
    loadMarkers(url) {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', url, true);
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

    renderVisibleMarkers() {
        // Remove existing markers
        this.markers.forEach(marker => this.map.removeLayer(marker));
        this.markers = [];

        // Get current map bounds
        let bounds = this.map.getBounds();
        this.allData.forEach((pets) => {
            // Only add marker if it's within the visible area
            if (!bounds.contains([pets.latitude, pets.longitude])) {
                return;
            }
            let markerText = `
                      <div class="pet-marker mt-5 mb-5">
                        <div><img src="${pets.photo_url}" alt="${pets.name}"/></div>
                        <p class="pet-name">Pet name: ${pets.name}</p>
                        <p class="pet-location">Last seen: ${pets.latitude} ${pets.longitude}</p>
                        <p class="pet-status">Status: ${pets.status}</p>
                        <p class="pet-sighting">Previous Sighting: ${pets.comment}</p>
                        ${isLoggedIn ? `
                        <form>
                           <input type="hidden" name="pet-id" value="${pets.id}"/>
                           <input type="text" name="sighting-comment" class="w-100 mb-2" placeholder="Where did you see the pet?" required/>
                           <button type="submit" class="btn btn-primary"> Add Sighting </button>
                        </form>` : '<p class="text-muted">Log in to add a sighting</p>'}
                      </div>
                    `;
            let marker = L.marker([pets.latitude, pets.longitude])
                .addTo(this.map)
                .bindPopup(markerText, { autoClose: true, closeOnClick: true })
                // prevent page reload using dom
                .on('mouseover', event => {
                    event.target.openPopup();
                });
            marker.petData = { latitude: pets.latitude, longitude: pets.longitude };
            if (isLoggedIn) {
                marker.on('popupopen', () => {
                    let popup = marker.getPopup().getElement();
                    let form = popup.querySelector('form');
                    if (form) {
                        form.addEventListener('submit', function (e) {
                            e.preventDefault();
                            let comment = form.querySelector('input[name="sighting-comment"]').value.trim();
                            let petId = form.querySelector('input[name="pet-id"]').value;

                            if (!comment) {
                                alert("Please enter a comment.");
                                return;
                            }


                            var sightingXhr = new XMLHttpRequest();
                            // Ajax endpoint 1
                            sightingXhr.open('POST', 'sightings.php', true);
                            sightingXhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                            sightingXhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                            sightingXhr.send('pet-id=' + petId
                                + '&sighting-comment=' + encodeURIComponent(comment)
                                + '&latitude=' + marker.petData.latitude
                                + '&longitude=' + marker.petData.longitude
                            );

                            sightingXhr.onreadystatechange = () => {
                                if (sightingXhr.readyState === 4) {
                                    if (sightingXhr.status === 200) {
                                        form.querySelector('input[name="sighting-comment"]').value = '';
                                        alert('Sighting added successfully.');
                                    } else {
                                        alert('Failed to add sighting.')
                                    }
                                }
                            };
                        });
                    }
                });
            }
            this.markers.push(marker);
        });
    }
}
