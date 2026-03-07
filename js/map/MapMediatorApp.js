/**
 * This class implement the Mediator pattern.
 * <p>
 *     Instead of each class talking to each other which creates many to many relation and often can be confusing.
 *     This class mediate between class and enable each class talk to each other.
 *     Therefore we can implement many to one relation and keep logic clean.
 *     Note: We use import to add other class to this class. The idea is how java works in OOP. We use that idea here.
 * </p>
 */
import {PetMap} from './PetMap.js';
import {SightingList} from './SightingList.js'
// import {SearchValidation} from './map/SearchValidation.js'
import {Geolocation} from './Geolocation.js'
import {SightingsMapAndListAjax} from "./SightingsMapAndListAjax.js";

class MapMediatorApp {
    constructor() {
        this.ajax = new SightingsMapAndListAjax()
        this.geolocation = new Geolocation();
        this.defaultLat = 53.4631;
        this.defaultLng = -2.2913;
        this.petMap = new PetMap('map', this.defaultLat, this.defaultLng, 16, this.geolocation, this.ajax);
        this.sightingList = new SightingList('sighting-container', this.petMap, this.ajax);
    }

    initialise() {
        let params = new URLSearchParams(window.location.search);
        let focusLat = parseFloat(params.get('lat'));
        let focusLng = parseFloat(params.get('lng'));
        let focusPetId = params.get('focusPet');

        this.ajax.fetchSightings(
            (data) => {
                this.petMap.setPetDataOnMap(data);
                this.sightingList.setSightingData(data);

                // After data is loaded, focus on the searched pet
                if (focusPetId && focusLat && focusLng) {
                    this.petMap.map.setView([focusLat, focusLng], 18);
                    // Wait for moveend re-render to finish, then open popup
                    setTimeout(() => {
                        this.petMap.markers.forEach(marker => {
                            if (marker.petId == focusPetId) {
                                marker.openPopup();
                            }
                        });
                    }, 500);
                }
            },
            (error) => {
                console.log(error);
            }
        );

        this.petMap.onSightingAdded = () => {
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
        };

        // Only use geolocation if not redirected from search
        if (!focusPetId) {
            this.geolocation.locate(
                (lat, lng) => {
                    this.petMap.map.setView([lat, lng], 16);
                    this.geolocation.showUserLocation(this.petMap.map, lat, lng);
                },
                () => {}
            );
        }
    }

}

const app = new MapMediatorApp();
app.initialise();