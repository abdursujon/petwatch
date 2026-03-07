/**
 * SearchBar handles the live search UI, debouncing, and result rendering.
 * Uses SearchAjax for server communication and SearchValidation for input sanitisation.
 */
export class LiveSearchUI {
    constructor(inputId, resultsId, searchAjax, searchValidation) {
        this.input = document.getElementById(inputId);
        this.resultsContainer = document.getElementById(resultsId);
        this.ajax = searchAjax;
        this.validation = searchValidation;
        this.debounceTimer = null;
        this.debounceDelay = 300;
        this.currentPage = 1;
        this.limit = 10;
        this.initListeners();
    }

    /**
     * Initialise event listeners on the search input.
     * Debounces keyup so we don't fire a request on every keystroke.
     */
    initListeners() {
        this.input.addEventListener('keyup', () => {
            clearTimeout(this.debounceTimer);
            this.debounceTimer = setTimeout(() => {
                this.currentPage = 1;
                this.performSearch();
            }, this.debounceDelay);
        });

        // Close results when clicking outside
        document.addEventListener('click', (e) => {
            if (!this.input.contains(e.target) && !this.resultsContainer.contains(e.target)) {
                this.resultsContainer.innerHTML = '';
            }
        });
    }

    /**
     * Validates input and calls the search endpoint.
     */
    performSearch() {
        let query = this.validation.validateQuery(this.input.value);

        if (!query) {
            this.resultsContainer.innerHTML = '';
            return;
        }

        this.ajax.fetchSuggestions(query,
            (suggestions) => {
                this.renderSuggestions(suggestions, query);
            },
            (error) => {
                console.error(error);
            }
        );
    }

    /**
     * Renders autocomplete suggestions dropdown.
     * Highlights the matching part of the pet name.
     */
    renderSuggestions(suggestions, query) {
        this.resultsContainer.innerHTML = '';

        if (suggestions.length === 0) {
            this.resultsContainer.innerHTML = '<div class="search-no-results">No results found</div>';
            return;
        }

        let regex = new RegExp('(' + query + ')', 'gi');

        suggestions.forEach((pet) => {
            let item = document.createElement('div');
            item.className = 'search-result-item';

            let highlightedName = pet.name.replace(regex, '<strong>$1</strong>');
            let highlightedSpecies = pet.species.replace(regex, '<strong>$1</strong>');
            let highlightedBreed = pet.breed.replace(regex, '<strong>$1</strong>');
            let highlightedColor = pet.color.replace(regex, '<strong>$1</strong>');
            let highlightedComment = pet.comment.replace(regex, '<strong>$1</strong>');
            let highlightedAddress = pet.address ? pet.address.replace(regex, '<strong>$1</strong>') : '';

            item.innerHTML = `
                  <img src="${pet.photo_url}" alt="${pet.name}" class="search-result-img"/>
                  <div class="search-result-info">
                      <span class="search-result-name">${highlightedName}</span>
                      <span class="search-result-species">${highlightedSpecies} · ${highlightedBreed} · ${highlightedColor}</span>
                      <span class="search-result-comment">${highlightedComment}</span>
                      <span class="search-result-address">${highlightedAddress}</span>
                  </div>
              `;

            item.addEventListener('click', () => {
                let petId = this.validation.validatePetId(pet.id);
                if (!petId) return;

                this.ajax.fetchPetById(petId,
                    (petData) => {
                        this.resultsContainer.innerHTML = '';
                        this.input.value = '';
                        this.onResultClick(petData);
                    },
                    (error) => {
                        console.error(error);
                    }
                );
            });

            this.resultsContainer.appendChild(item);
        });

        let viewAll = document.createElement('div');
        viewAll.className = 'search-view-all';
        viewAll.textContent = 'View all results for "' + query + '"';
        viewAll.addEventListener('click', () => {
            this.showFullResults(query);
        });
        this.resultsContainer.appendChild(viewAll);
    }

    /**
     * Fetches full paginated search results.
     * Called when user clicks "View all results".
     */
    showFullResults(query) {
        query = this.validation.validateQuery(query);
        if (!query) return;

        this.ajax.searchPets(query, '', '', this.currentPage, this.limit,
            (data) => {
                this.renderFullResults(data, query);
            },
            (error) => {
                console.error(error);
            }
        );
    }

    /**
     * Renders the full search results with pagination info.
     */
    renderFullResults(data, query) {
        this.resultsContainer.innerHTML = '';

        if (data.results.length === 0) {
            this.resultsContainer.innerHTML = '<div class="search-no-results">No results found</div>';
            return;
        }

        data.results.forEach((pet) => {
            let item = document.createElement('div');
            item.className = 'search-result-item search-full-result';

            let highlightedName = pet.name.replace(
                new RegExp('(' + query + ')', 'gi'),
                '<strong>$1</strong>'
            );

            item.innerHTML = `
                  <img src="${pet.photo_url}" alt="${pet.name}" class="search-result-img"/>
                  <div class="search-result-info">
                      <span class="search-result-name">${highlightedName}</span>
                      <span class="search-result-species">${pet.species} - ${pet.breed}</span>
                      <span class="search-result-address">${pet.address || 'Unknown location'}</span>
                  </div>
              `;

            item.addEventListener('click', () => {
                this.resultsContainer.innerHTML = '';
                this.input.value = '';
                this.onResultClick(pet);
            });

            this.resultsContainer.appendChild(item);
        });

        // Pagination — show "Load more" if there are more results
        if (data.page * data.limit < data.total) {
            let loadMore = document.createElement('div');
            loadMore.className = 'search-load-more';
            loadMore.textContent = 'Load more results (' + data.total + ' total)';
            loadMore.addEventListener('click', () => {
                this.currentPage++;
                this.showFullResults(query);
            });
            this.resultsContainer.appendChild(loadMore);
        }
    }

    /**
     * Callback when a search result is clicked.
     * Set from outside (e.g. MediatorApp) to connect search to the map.
     */
    onResultClick(petData) {
        // Default — overridden by MediatorApp to pan map to pet location
    }
}