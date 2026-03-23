/**
 * LiveSearchAjax class handles all AJAX communication for the live search feature.
 * <p>
 *  This class is designed to be imported in the LiveSearchMediatorApp.js class
 *  by adding the export keyword to the class declaration.
 *  This helps us develop a Mediator Design Pattern where possible, establishing a
 *  many-to-one relationship through a central JavaScript app file.
 * </p>
 * This class handles total three AJAX endpoint, they are:
 * <p>
 *  1. Endpoint one: searchPets() searches pets with filters and pagination
 *  2. Endpoint two: fetchSuggestions() handles autocomplete suggestions
 *  3. Endpoint three: fetchPetById() fetches single pet detail
 * </p>
 */
export class LiveSearchAjax {
  constructor() {}

  /**
   * Ajax endpoint 1 (GET) — Search pets with query (species, status etc.), filters, and pagination.
   * Returns ranked, paginated results from the server.
   */
  searchPets(query, species, status, page, limit, onSuccess, onError) {
    var xhr = new XMLHttpRequest();

    // Build the search endpoint url with encoded query parameters and AJAx token by calling the php endpoint SearchPets.
    var url = 'js/live-search/live-search-endpoints/SearchPets.php?q=' + encodeURIComponent(query)
      + '&species=' + encodeURIComponent(species)
      + '&status=' + encodeURIComponent(status)
      + '&page=' + page
      + '&limit=' + limit
      + '&token=' + ajaxToken;

    xhr.open('GET', url, true);
    xhr.onreadystatechange = () => {
      if (xhr.readyState === 4) {
        if (xhr.status !== 200) {
          onError('Search failed. Status: ' + xhr.status);
          return;
        }
        try {
          let data = JSON.parse(xhr.responseText);
          onSuccess(data);
        } catch (e) {
          onError('Invalid JSON response from search.');
        }
      }
    };
    xhr.send();
  }


  /**
   * Ajax endpoint 2 (GET) — Fetch autocomplete suggestions.
   * Returns only pet names, IDs, species, and photo for the dropdown.
   */
  fetchSuggestions(query, onSuccess, onError) {
    var xhr = new XMLHttpRequest();
    var url = 'js/live-search/live-search-endpoints/SearchSuggestions.php?q=' + encodeURIComponent(query)
      + '&token=' + ajaxToken;

    xhr.open('GET', url, true);
    xhr.onreadystatechange = () => {
      if (xhr.readyState === 4) {
        if (xhr.status !== 200) {
          onError('Suggestions failed. Status: ' + xhr.status);
          return;
        }
        try {
          let data = JSON.parse(xhr.responseText);
          onSuccess(data);
        } catch (e) {
          onError('Invalid JSON response from suggestions.');
        }
      }
    };
    xhr.send();
  }


  /**
   * Ajax endpoint 3 (GET) — Fetch a single pet's full details by ID.
   * Used when user clicks a search result to view on the map.
   */
  fetchPetById(petId, onSuccess, onError) {
    var xhr = new XMLHttpRequest();
    var url = 'js/live-search/live-search-endpoints/FetchPetById.php?id=' + petId
      + '&token=' + ajaxToken;

    xhr.open('GET', url, true);
    xhr.onreadystatechange = () => {
      if (xhr.readyState === 4) {
        if (xhr.status !== 200) {
          onError('Failed to fetch pet. Status: ' + xhr.status);
          return;
        }
        try {
          let data = JSON.parse(xhr.responseText);
          onSuccess(data);
        } catch (e) {
          onError('Invalid JSON response.');
        }
      }
    };
    xhr.send();
  }
}