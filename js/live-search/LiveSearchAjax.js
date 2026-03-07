/**
 * SearchAjax handles all Ajax communication for the live search feature.
 * 1. Endpoint one: searchPets() searches pets with filters and pagination
 * 2. Endpoint two: fetchSuggestions() lightweight autocomplete suggestions
 * 3. Endpoint three: fetchPetById() fetches single pet detail
 */
export class LiveSearchAjax {
    constructor() {
    }

    /**
     * Ajax endpoint 1 (GET) — Search pets with query, filters, and pagination.
     * Returns ranked, paginated results from the server.
     */
    searchPets(query, species, status, page, limit, onSuccess, onError) {
        var xhr = new XMLHttpRequest();
        var url = 'js/live-search/ajax-php-call/SearchPets.php?q=' + encodeURIComponent(query)
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
     * Ajax endpoint 2 (GET) — Fetch lightweight autocomplete suggestions.
     * Returns only pet names, IDs, species, and photo for the dropdown.
     */
    fetchSuggestions(query, onSuccess, onError) {
        var xhr = new XMLHttpRequest();
        var url = 'js/live-search/ajax-php-call/SearchSuggestions.php?q=' + encodeURIComponent(query)
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
        var url = 'js/live-search/ajax-php-call/FetchPetById.php?id=' + petId
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