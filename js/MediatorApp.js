/**
 * This class implement the Mediator pattern.
 * <p>
 *     Instead of each class talking to each other which creates many to many relation and often can be confusing.
 *     This class mediate between class and enable each class talk to each other.
 *     Therefore we can implement many to one relation and keep logic clean.
 *     Note: We use import to add other class to this class. The idea is how java works in OOP. We use that idea here.
 * </p>
 */
import {PetMap} from './map/PetMap.js';
import {SightingList} from './map/SightingList.js'
// import {Validation} from './map/Validation.js'
import {Geolocation} from './map/Geolocation.js'
import {SightingsMapAndListAjax} from "./map/SightingsMapAndListAjax.js";

class MediatorApp {
    constructor() {
        this.ajax = new SightingsMapAndListAjax()
        this.geolocation = new Geolocation();
        this.defaultLat = 53.4631;
        this.defaultLng = -2.2913;
        this.petMap = new PetMap('map', this.defaultLat, this.defaultLng, 16, this.geolocation, this.ajax);
        this.sightingList = new SightingList('sighting-container', this.petMap, this.ajax);
    }

    initialise() {
        this.ajax.fetchSightings(
            (data) => {
                this.petMap.setPetDataOnMap(data);
                this.sightingList.setSightingData(data);
            },
            (error) => {
                console.log(error);
            }
        );

        this.geolocation.locate(
            (lat, lng) => {
                this.petMap.map.setView([lat, lng], 16);
                this.geolocation.showUserLocation(this.petMap.map, lat, lng);
            },
            () => {
                // Geolocation denied or browser does not support it, stays default value.
            }
        );
    }
}

const app = new MediatorApp();
app.initialise();