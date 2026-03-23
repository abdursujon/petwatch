/**
 * This class implement the Mediator pattern for the pet map and sighting lists.
 * Aim is to centralise communication between classes creating many to one relation
 * instead of each class talking to each other which creates many to many relation
 * and maintainance becomes increasingly impossible.
 * <p>
 *   Created intances of SightingsMapAndListAjax, Geolocation, PetMap, SightingList to
 *   utilise their methods to develop the pet map and lists features.
 * </p>
 */
import {PetMap} from './PetMap.js';
import {SightingList} from './SightingList.js'
import {Geolocation} from './Geolocation.js'
import {SightingsMapAndListAjax} from "./SightingsMapAndListAjax.js";
import {MapAndSightingDataValidation} from './MapAndSightingDataValidation.js';

class MapMediatorApp {

  // Constructor creating intances of all pet map and lists relevant classes with default pet map location in Salford.
  constructor() {
    this.ajax = new SightingsMapAndListAjax()
    this.geolocation = new Geolocation();
    this.defaultLat = 53.4872;
    this.defaultLng = -2.2737;
    this.petMap = new PetMap('map', this.defaultLat, this.defaultLng, 16);
    this.sightingList = new SightingList('sighting-container');
  }


  /**
   * Inialise the pet map and lists feature by loading data and wiring
   * all communication through this central app method.
   */
  initialise() {
    // Parse url query string to check if user was redirected from live search with a pet to focus on.
    let params = new URLSearchParams(window.location.search);
    let focusLat = parseFloat(params.get('lat'));
    let focusLng = parseFloat(params.get('lng'));
    let focusPetId = params.get('focusPet');

    // Validate URL parameters before use.
    if (focusPetId && MapAndSightingDataValidation.validatePetId(focusPetId)) {
      focusPetId = null;
    }

    // If the lat/lng from url invalid, sets them to NaN (not a number).
    if (MapAndSightingDataValidation.validateCoordinates(focusLat, focusLng)) {
      focusLat = NaN;
      focusLng = NaN;
    }

    // Fetch all sightings data through PetMap and SightingList class methods.
    this.ajax.fetchSightings(
      (data) => {
        this.petMap.setPetDataOnMap(data);
        this.sightingList.setSightingData(data);

        // After data is loaded, focus on the searched pet
        if (focusPetId && focusLat && focusLng) {
          this.petMap.map.flyTo([focusLat, focusLng], 18);

          // Wait for fly animation to finish before opening popup on the pet map.
          this.petMap.map.once('moveend', () => {
            let targetMarker = this.petMap.markers.find(m =>
              m.petId == focusPetId
              && m.getLatLng().lat.toFixed(4) === focusLat.toFixed(4)
              && m.getLatLng().lng.toFixed(4) === focusLng.toFixed(4)
            );
            if (targetMarker) {
              this.petMap.clusterGroup.zoomToShowLayer(targetMarker, () => {
                targetMarker.openPopup();
              });
            }
          });

          // Remove search params from URL so reload doesn't re-trigger focus
          window.history.replaceState({}, '', window.location.pathname);
        }
      },
      (error) => {
        console.log(error);
      }
    );

    // Calls method of SightingsMapAndListAjax method submitNewSighting to post the new sighting
    // Then refresh map and list data on success to update the map with new sightings without page reload.
    this.petMap.onSightingSubmit = (petId, comment, lat, lng, address) => {
      this.ajax.submitNewSighting(petId, comment, lat, lng,
        (result) => {
          alert('Sighting added successfully.');
          this.ajax.cachedSightings = null;
          this.ajax.fetchSightings(
            (data) => {
              this.petMap.setPetDataOnMap(data);
              this.sightingList.setSightingData(data);
            },
            (error) => { console.log(error); }
          );
        },
        (error) => {
          alert(error);
        }
      );
    };

    // Routes the PetMap geocode request through ajax to convert coordinates to a readable address.
    this.petMap.onReverseGeocode = (lat, lng, onSuccess, onError) => {
      this.ajax.reverseLatLngToHumanReadableAddress(lat, lng, onSuccess, onError);
    };

    // Provide GPS location to PetMap when user clicks "Use Your Location" during sighting creation
    this.petMap.onUseMyLocation = () => {
      if (this.geolocation.lat && this.geolocation.lng) {
        this.petMap.setSightingLocation(this.geolocation.lat, this.geolocation.lng);
      } else {
        alert('Could not get your location. Please check if you allowed location or click on the map instead to choose a location.');
      }
    };

    // Fly map to user's GPS location when "Select Your Location" button is clicked
    this.petMap.onLocateMe = () => {
      if (this.geolocation.lat && this.geolocation.lng) {
        this.petMap.map.flyTo([this.geolocation.lat, this.geolocation.lng], 16);
      } else {
        alert('Please allow location access in your browser settings to use this feature.');
      }
    };

    // When a sighting card is clicked, fly map to that pet and open popup
    this.sightingList.onCardClick = (pet) => {
      document.getElementById('map').scrollIntoView({behavior: 'smooth'});
      this.petMap.map.flyTo([pet.latitude, pet.longitude], 18);
      setTimeout(() => {
        this.petMap.markers.forEach(marker => {
          if (marker.petId == pet.id) {
            marker.openPopup();
          }
        });
      }, 800);
    };

    // When "Add Sighting" is clicked from a card, enter sighting mode on map
    this.sightingList.onCreateSighting = (petId) => {
      document.getElementById('map').scrollIntoView({behavior: 'smooth'});
      this.petMap.enterCreateSightingMode(petId);
    };

    // Start GPS tracking so select you location button work accordingly
    // Only auto center on user location if not routed from live search
    let hasCentered = false;
    this.geolocation.startTracking(this.petMap.map, (lat, lng) => {
      if (!hasCentered && !focusPetId) {
        this.petMap.map.flyTo([lat, lng], 16);
        hasCentered = true;
      }
    });

    // Real time update on the map without page reload.
    // If other user create new sighting the marker will show on the map without needing any page reload
    setInterval(() => {
      this.ajax.cachedSightings = null;
      this.ajax.fetchSightings(
        (data) => {
          this.petMap.setPetDataOnMap(data);
          this.sightingList.setSightingData(data);
        },
        (error) => {
          console.log(error);
        }
      );
    }, 30000);
  }
}

const app = new MapMediatorApp();
app.initialise();