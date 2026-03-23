/**
 * This is the central app which combines all relevant JavaScript files for the live search feature
 * through importing them.
 * <p>
 *  This design refers to the Mediator Design Pattern, where different classes are used
 *  to build one central app. The mediator coordinates communication between LiveSearchUI,
 *  LiveSearchAjax, and SearchValidation so that no class talks directly talks to each other.
 *  This design pattern helps us build many to one relation, which is vital for scalability in future.
 * </p>
 * <p>
 *  The search is avaiable on every page from the header instead of only on the pet map page, which improves usability.
 *  When the user clicks a result after searching, the result is shown on the pet map where the
 *  marker also auto opens.
 * </p>
 */
import {LiveSearchAjax} from './LiveSearchAjax.js';
import {SearchValidation} from './SearchValidation.js';
import {LiveSearchUI} from './LiveSearchUI.js';

class LiveSearchMediatorApp {

  // Constructor to create instance of imported classes which we use to call their methods.
  constructor() {
    this.liveSearchAjax = new LiveSearchAjax();
    this.searchValidation = new SearchValidation();
    this.liveSearchUi = new LiveSearchUI('live-search-input', 'live-search-results');
  }


  /**
   * Binds all UI events to the mediator so that communication between
   * LiveSearchUI, LiveSearchAjax, and SearchValidation flows through one app.
   */
  initialise() {
    // When user types in the search input, validate and fetch suggestions.
    this.liveSearchUi.onSearchInput = (rawQuery) => {
      // Use methods of SearchValidation class to validate query.
      let query = this.searchValidation.validateQuery(rawQuery);

      // If the query is invalid such as short, empty or no match, clears the search results dropdown.
      if (!query) {
        this.liveSearchUi.clearResults();
        return;
      }

      // Calls the method of LiveSearchAjax class to fetch search suggetions, and render.
      this.liveSearchAjax.fetchSuggestions(query,
        (suggestions) => {
          this.liveSearchUi.renderSuggestions(suggestions, query);
        },
        (error) => {
          console.error(error);
        }
      );
    };

    // When user clicks a suggestion, validate ID and fetch full pet data then redirect
    this.liveSearchUi.onResultSelect = (petId) => {
      petId = this.searchValidation.validatePetId(petId);
      if (!petId) return;

      // Use LiveSearchAjax class method fetchPetById to get specific pet information.
      this.liveSearchAjax.fetchPetById(petId,
        (petData) => {
          this.liveSearchUi.clearResults();
          this.liveSearchUi.clearInput();
          window.location.href = 'viewSightings.php?focusPet=' + petData.id
            + '&lat=' + petData.latitude
            + '&lng=' + petData.longitude;
        },
        (error) => {
          console.error(error);
        }
      );
    };

    // When user clicks "View all results", validate and fetch paginated results
    this.liveSearchUi.onViewAll = (rawQuery) => {
      let query = this.searchValidation.validateQuery(rawQuery);
      if (!query) return;

      this.liveSearchAjax.searchPets(query, '', '',
        this.liveSearchUi.currentPage, this.liveSearchUi.limit,
        (data) => {
          this.liveSearchUi.renderFullResults(data, query);
        },
        (error) => {
          console.error(error);
        }
      );
    };

    // When user clicks a result in full results view, redirect to map
    this.liveSearchUi.onResultClick = (petData) => {
      this.liveSearchUi.clearResults();
      this.liveSearchUi.clearInput();
      window.location.href = 'viewSightings.php?focusPet=' + petData.id
        + '&lat=' + petData.latitude
        + '&lng=' + petData.longitude;
    };
  }
}

// Create and initialise the live search mediator app which implements the entire live search feature.
const app = new LiveSearchMediatorApp();
app.initialise();