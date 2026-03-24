/**
 * The PetMap class handles rendering for the leaflet pet map.
 * Displays the pet marker and manage the create pet sightings panel on the map.
 * It also provides geolocation button "Select Your Location" helping focus on user location
 * when they pan out from their location.
 */
import {MapAndSightingDataValidation} from './MapAndSightingDataValidation.js';

export class PetMap {

  /**
   * Constructor initalise the leaflet pet map, with tile layer, marker clustering and UI controls etc.
   */
  constructor(elementId, lat, lng, zoom) {
    this.map = L.map(elementId, {maxZoom: 19}).setView([lat, lng], zoom);
    this.popupOption = {"closeButton": false};
    this.markers = []; // stores all marker objects on the map
    this.allData = []; // stores all pet data from ajax
    this.sightingMode = false // tracks if user on sighting mode to add a new sighting
    this.clusterGroup = L.markerClusterGroup();
    this.map.addLayer(this.clusterGroup);
    this.initialTileLayer();
    this.addLocateMeButton();
    if (typeof isLoggedIn !== 'undefined' && isLoggedIn) {
      this.initialiseSightingButtonDelegate();
    }
  }


  // Load OpenStreetMap tile layer onto the map with max zoom of 19
  initialTileLayer() {
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 19,
      attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OPenStreetMap</a> contributors'
    }).addTo(this.map);
  }


  // Stores sighting data from the mediator and renders marker on the map
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

      // Build popup HTML card, create sighting button only shows if user is logged in.
      let markerText = `                                                                                                                                                                                                          
          <div class="pet-marker mb-3">
            <img src="${MapAndSightingDataValidation.escapeHTML(pets.photo_url)}" 
            alt="${MapAndSightingDataValidation.escapeHTML(pets.name)}"/>
            <div class="p-2">                                                                                                                                                                                                       
              <p class="pet-name fw-bold mb-1">${MapAndSightingDataValidation.escapeHTML(pets.name)}</p>
              <span class="badge ${pets.status === 'lost' ? 'bg-danger' : 'bg-success'} mb-1">
                ${MapAndSightingDataValidation.escapeHTML(pets.status)}
              </span>                                                                        
              <p class="pet-location mb-1">
              Last seen: ${MapAndSightingDataValidation.escapeHTML(pets.address) || 'Unknown location'}
              </p>                                                                                            
              <p class="pet-sighting mb-1">${MapAndSightingDataValidation.escapeHTML(pets.comment)}</p>                                                                                                                             
              ${isLoggedIn ? `                                                                                                                                                                                                      
              <input type="hidden" name="pet-id" value="${MapAndSightingDataValidation.escapeHTML(pets.id)}"/>                                                                                                                      
              <button type="submit" class="btn btn-primary btn-sm py-0 w-75 add-sighting-btn text-start mt-1" style="font-size: 14px;">
              Create Sighting
              </button>                                                                     
              ` : '<p class="text-muted mb-0"><small>Log in to create sighting</small></p>'}                                                                                                                                        
            </div>                                                                                                                                                                                                                  
          </div>                                                                                                                                                                                                                    
        `;

      // Create a custom icon for pet markers using each pets own image.
      let petIcon = L.divIcon({
        html: `<img src="${MapAndSightingDataValidation.escapeHTML(pets.photo_url)}" 
               alt="${MapAndSightingDataValidation.escapeHTML(pets.name)}" />`,
        className: 'pet-marker-icon',
        iconSize: [40, 52],
        iconAnchor: [20, 52],
        popupAnchor: [0, -52]
      });

      // Create a marker at the pet coordinates, and add it to cluster group.
      // Also bind the popup card autoPan settings and open the popup on hover
      let marker = L.marker([pets.latitude, pets.longitude], {icon: petIcon})
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
   * Enters create sighting mode for the selected pet by user.
   * Clear existing markers and popups.
   * Then build a floating panel with the selected pet information.
   * On submit, validates comment, coordinates, and pet id.
   * Next it notifies of the change though central app MediatorMap.
   * On cancel, exits sighting mode and restores the map with markers.
   */
  enterCreateSightingMode(petId) {
    this.sightingMode = true;
    this.sightingPetId = petId;
    this.sightingLatLong = null; // Holds the location user click
    this.sightingMarker = null;

    let existingPanel = document.getElementById('sighting-panel');
    if (existingPanel) existingPanel.remove();

    // Close the pet popup so user can interact with the pet map
    this.map.closePopup();
    this.clusterGroup.clearLayers();

    // Fetch the pet data chose by user to show it's name and photo in the panel
    let pet = this.allData.find(p => p.id == petId);

    // Create floating panel UI on top of the map
    let panel = document.createElement('div');
    panel.id = 'sighting-panel';
    panel.className = 'shadow-lg py-3 px-3'
    panel.innerHTML = `                                                                                                                                                                                                               
      <div class="sighting-pet-info create-pet mb-3">                                                                                                                                                                                   
        <img src="${MapAndSightingDataValidation.escapeHTML(pet.photo_url)}" alt="${MapAndSightingDataValidation.escapeHTML(pet.name)}" />                                                                                            
        <h6 class="mt-2 mb-0 fw-bold">${MapAndSightingDataValidation.escapeHTML(pet.name)}</h6>                                                                                                                                       
      </div> 
      <h6 class="fw-bold">Create New Sighting</h6>
      <p class="mb-2 font-bold" style="font-size: 13px;">📍 Click on the map to report pet location.</p>
      <button id="sighting-use-location" class="btn btn-outline-primary btn-sm mb-2 w-100">
          Or Use Your Location
      </button>
      <p id="sighting-coords" class="text-muted mb-2" style="font-size: 12px;">No location selected</p>
      <input type="text" id="sighting-comment" class="form-control form-control-sm mb-3" placeholder="Add a comment..." required/>
      <div class="d-flex gap-2">
          <button id="sighting-submit" class="btn btn-primary btn-sm flex-grow-1" disabled>Submit</button>
          <button id="sighting-cancel" class="btn btn-secondary btn-sm flex-grow-1">Cancel</button>
      </div>
  `;

    this.map.getContainer().style.position = 'relative';
    this.map.getContainer().after(panel);

    if (window.innerWidth <= 1279) {
      panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    panel.addEventListener('click', (e) => {
      e.stopPropagation();
    });

    // Option 1: Click on the map to pick a location
    this.onMapClick = (e) => {
      this.setSightingLocation(e.latlng.lat, e.latlng.lng);
    };
    this.map.on('click', this.onMapClick);

    // Option 2: Use GPS current geolocation from tracking.
    document.getElementById('sighting-use-location').addEventListener('click', () => {
      if (this.onUseMyLocation) {
        this.onUseMyLocation();
      }
    });

    // Validate user input, then notify mediator to submit the new sighting to the server.
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

      // Notify mediator to handle submission
      if (this.onSightingSubmit) {
        this.onSightingSubmit(
          this.sightingPetId,
          comment,
          this.sightingLatLong.lat,
          this.sightingLatLong.lng,
          this.sightingAddress || ''
        );
      }
      this.exitSightingMode();
    });

    document.getElementById('sighting-cancel').addEventListener('click', () => {
      this.exitSightingMode();
    });
  }


  /**
   * Set the sighting location chosen by the user through click on the map or geolocation.
   * Stores the coordinates, displays them on the panel to the user.
   * The coordiantes are converted to human readable address on the panel which user can view.
   * Enable the submit button when location is selected.
   * A temporary marker on the map shows when user select any location before submit.
   */
  setSightingLocation(lat, lng) {
    this.sightingLatLong = {lat: lat, lng: lng};
    let coordsEl = document.getElementById('sighting-coords');
    coordsEl.textContent = `${lat.toFixed(5)}, ${lng.toFixed(5)}`;

    if (this.onReverseGeocode) {
      this.onReverseGeocode(lat, lng, (address) => {
        coordsEl.textContent = address;
        this.sightingAddress = address;
      }, () => {
        this.sightingAddress = lat.toFixed(5) + ', ' + lng.toFixed(5);
      });
    }

    // Enable submit when location is set
    document.getElementById('sighting-submit').disabled = false;
    // Remove old temporary marker if user re-clicks on a different location on the map
    if (this.sightingMarker) {
      this.map.removeLayer(this.sightingMarker);
    }
    this.sightingMarker = L.marker([lat, lng]).addTo(this.map);
  }


  /**
   * Exit create sighting mode.
   * This method also remove the temporary map click listener, clear the temporary markers.
   * It also clear the create sighting panel, then re-render the pet markers on the map.
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


  /**
   * Uses event delegation on the map container to listen for "Create Sighting" button clicks.
   * When button is clicked, extract the pet id from the hidden input and enters the create sighting mode
   * for that specific pet that user selected.
   */
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

  /**
   * Wires the "Select Your Location" button to notify the mediator map app
   * through onLocateMe() callback when user click the button.
   * This method helps us re-focus on the user location when user pan out from their location.
   */
  addLocateMeButton() {
    let btn = document.getElementById('locate-me-btn');
    if (!btn) return;

    btn.addEventListener('click', () => {
      if (this.onLocateMe) {
        this.onLocateMe();
      }
    });
  }
}