/**
 * SearchInit wires up the live search feature.
 * Runs on every page from the header.
 * When a result is clicked, redirects to viewSightings and focuses the map on that pet.
 */
import { LiveSearchAjax } from './LiveSearchAjax.js';
import { SearchValidation } from './SearchValidation.js';
import { LiveSearchUI } from './LiveSearchUI.js';

class LiveSearchMediatorApp {
    constructor() {
        this.ajax = new LiveSearchAjax();
        this.validation = new SearchValidation();
        this.LiveSearchUI = new LiveSearchUI('live-search-input', 'live-search-results', this.ajax, this.validation);
    }

    initialise() {
        this.LiveSearchUI.onResultClick = (petData) => {
            window.location.href = 'viewSightings.php?focusPet=' + petData.id
                + '&lat=' + petData.latitude
                + '&lng=' + petData.longitude;
        };
    }
}

const app = new LiveSearchMediatorApp();
app.initialise();