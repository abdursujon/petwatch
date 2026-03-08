import {MapAndSightingDataValidation} from './MapAndSightingDataValidation.js';

export class PetMap {
  constructor(elementId, lat, lng, zoom, geolocation, ajax) {
    this.map = L.map(elementId, {maxZoom: 19}).setView([lat, lng], zoom);
    this.popupOption = {"closeButton": false};
    this.markers = []; // stores all marker objects on the map
    this.allData = []; // stores all pet data from ajax
    this.sightingMode = false // tracks if user on sighting mode to add a new sighting
    this.clusterGroup = L.markerClusterGroup();
    this.map.addLayer(this.clusterGroup);
    this.initialTileLayer();
    this.geolocation = geolocation;
    this.ajax = ajax;
    if (typeof isLoggedIn !== 'undefined' && isLoggedIn) {
      this.initialiseSightingButtonDelegate();
    }
  }

  initialTileLayer() {
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 19,
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
  }

  /**
   * Renders markers for only within visible area of the map.
   * This method helps us avoid loading hundreds of pets markers at once on the map which slows the map rendering by a lot.
   * Popup shows pet info for all users.
   * If user is logged in, show "Add A New Sightings" button otherwise show "Log in to add a sighting".
   */
  renderVisibleMarkers() {
    // Clear old markers from the map
    this.clusterGroup.clearLayers();
    this.markers = [];
    this.allData.forEach((pets) => {

      // Build popup HTML card, create a new sightings button only shows user is logged in.
      let markerText = `
      <div class="pet-marker mb-3">
          <img src="${MapAndSightingDataValidation.escapeHTML(pets.photo_url)}" alt="${MapAndSightingDataValidation.escapeHTML(pets.name)}"/>                                                                                       
          <div class="p-2">
              <p class="pet-name fw-bold mb-1">${MapAndSightingDataValidation.escapeHTML(pets.name)}</p>                                                                                                                            
              <span class="badge ${pets.status === 'lost' ? 'bg-danger' : 'bg-success'} mb-1">${MapAndSightingDataValidation.escapeHTML(pets.status)}</span>
              <p class="pet-location mb-1">Last seen: ${MapAndSightingDataValidation.escapeHTML(pets.address) || 'Unknown location'}</p>
              <p class="pet-sighting mb-1">${MapAndSightingDataValidation.escapeHTML(pets.comment)}</p>
              ${isLoggedIn ? `
              <input type="hidden" name="pet-id" value="${MapAndSightingDataValidation.escapeHTML(pets.id)}"/>
              <button type="submit" class="btn btn-primary btn-sm py-0 w-75 add-sighting-btn mt-1" style="font-size: 12px;">Add A New Sighting</button>   
              ` : '<p class="text-muted mb-0"><small>Log in to add a sighting</small></p>'}
          </div>
      </div>
  `;

      let marker = L.marker([pets.latitude, pets.longitude])
        .addTo(this.clusterGroup)
        .bindPopup(markerText, {
          autoClose: true,
          closeOnClick: true,
          autoPan: true,
          autoPanPaddingTopLeft: [50, 100],
          autoPanPaddingBottomRight: [50, 50]
        })

        .on('mouseover', event => {
          event.target.openPopup();
        });

      marker.petId = pets.id;
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
    this.clusterGroup.clearLayers();

    // Fetch the pet data chose by user to show it's name and photo in the panel
    let pet = this.allData.find(p => p.id == petId);

    // Create floating panel UI on top of the map
    let panel = document.createElement('div');
    panel.id = 'sighting-panel';
    panel.innerHTML = `                                                                                                                                                                                                               
      <div class="sighting-pet-info create-pet mb-3">                                                                                                                                                                                   
      <img src="${MapAndSightingDataValidation.escapeHTML(pet.photo_url)}" alt="${MapAndSightingDataValidation.escapeHTML(pet.name)}" />                                                                                            
      <h6 class="mt-2 mb-0 fw-bold">${MapAndSightingDataValidation.escapeHTML(pet.name)}</h6>                                                                                                                                       
  </div> 
      <h6 class="fw-bold">Create New Sighting</h6>
      <p class="mb-2 font-bold" style="font-size: 13px;">📍 Click on the map to report pet location.</p>
      <button id="sighting-use-location" class="btn btn-outline-primary btn-sm mb-2 w-100">
          Use my current location
      </button>
      <p id="sighting-coords" class="text-muted mb-2" style="font-size: 12px;">No location selected</p>
      <input type="text" id="sighting-comment" class="form-control form-control-sm mb-3" placeholder="Add a comment..." required/>
      <div class="d-flex gap-2">
          <button id="sighting-submit" class="btn btn-primary btn-sm flex-grow-1" disabled>Submit</button>
          <button id="sighting-cancel" class="btn btn-secondary btn-sm flex-grow-1">Cancel</button>
      </div>
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
      let commentError = MapAndSightingDataValidation.validateComment(comment);
      if (commentError) {
        alert(commentError);
        return;
      }
      let coordError = MapAndSightingDataValidation.validateCoordinates(
        this.sightingLatLong?.lat, this.sightingLatLong?.lng
      );
      if (coordError) {
        alert(coordError);
        return;
      }
      let petIdError = MapAndSightingDataValidation.validatePetId(this.sightingPetId);
      if (petIdError) {
        alert(petIdError);
        return;
      }

      // Send post request to record the sighting
      var sightingXhr = new XMLHttpRequest();
      sightingXhr.open('POST', 'createSightings.php', true);
      sightingXhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
      sightingXhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

      sightingXhr.onreadystatechange = () => {
        if (sightingXhr.readyState === 4) {
          if (sightingXhr.status === 200) {
            try {
              let result = JSON.parse(sightingXhr.responseText);
              if (result.success) {
                alert('Sighting added successfully.');
                if (this.onSightingAdded) {
                  this.onSightingAdded();
                }
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
        + '&address=' + encodeURIComponent(this.sightingAddress || '')
        + '&token=' + ajaxToken
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
    this.renderVisibleMarkers();
  }

  initialiseSightingButtonDelegate() {
    this.map.getContainer().addEventListener('click', (e) => {
      let btn = e.target.closest('.add-sighting-btn');
      if (!btn) return;
      let petIdInput = btn.closest('.pet-marker').querySelector('input[name="pet-id"]');
      if (petIdInput) {
        this.enterCreateSightingMode(petIdInput.value);
      }
    });
  }
}