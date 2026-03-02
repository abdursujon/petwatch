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
class MediatorApp {
    constructor() {
        this.geolocation = new Geolocation();
        this.defaultLat = 53.4631;
        this.defaultLong = -2.2913;
        this.petMap = new PetMap('map', this.defaultLat, this.defaultLong, 16, this.geolocation);
        this.sightingList = new SightingList('sighting-container', this.petMap);
    }

    initialise(){
        this.petMap.loadMarkers();
        this.geolocation.locate(
            (lat, long) => {
                this.petMap.map.setView([lat, long], 16);
                this.geolocation.showUserLocation(this.petMap.map, lat, long);
            },
            () =>{
                // Geolocation denied or browser does not support it, stays default value.
            }
        );
        this.sightingList.loadSightings();
    }
}

const app = new MediatorApp();
app.initialise();