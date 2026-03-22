/**
 * LiveSearchUI handles the live search DOM interactions, debouncing, and result rendering.
 * <p>
 *  This class does not call any AJAX endpoints or validate input directly.
 *  Instead, this class call empty callback method such as onSearchInput,
 *  onResultSelect, onViewAll, onResultClick
 *  which the mediator listens to and coordinates with other classes to implement live search UI feature.
 * </p>
 * <p>
 *  Rendering methods (renderSuggestions, renderFullResults) are called by the mediator
 *  after it receives data from the server.
 * </p>
 */
export class LiveSearchUI {
  constructor(inputId, resultsId) {
    this.input = document.getElementById(inputId);
    this.resultsContainer = document.getElementById(resultsId);
    this.debounceTimer = null;
    this.debounceDelay = 300;
    this.currentPage = 1;
    this.limit = 10;
    this.initListeners();
  }

  /**
   * Initialise event listeners on the search input.
   * Debouncing is implemented so we don't fire a request on every keystroke.
   * Search will only render when the user stops typing for 300ms (this.debounceDelay = 300).
   */
  initListeners() {
    this.input.addEventListener('keyup', () => {
      clearTimeout(this.debounceTimer);
      this.debounceTimer = setTimeout(() => {
        this.currentPage = 1;
        this.onSearchInput(this.input.value);
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
   * Clears the results container when the mediator app calls this method.
   * The reason is to remove the search result when not needed or invalid.
   */
  clearResults() {
    this.resultsContainer.innerHTML = '';
  }

  /**
   * Clears the input field when the mediator app calls this method.
   * The reason is to reset the search bar after the user clicks a result.
   */
  clearInput() {
    this.input.value = '';
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

    // Create a regex that matches all search query anywhere in a string, which is case insensitive.
    let regex = new RegExp('(' + query + ')', 'gi');

    suggestions.forEach((pet) => {
      let item = document.createElement('div');
      item.className = 'search-result-item';

      // Search result for pet name, species, breed, color, comment, and address
      let highlightedName = pet.name.replace(regex, '<strong>$1</strong>');
      let highlightedSpecies = pet.species.replace(regex, '<strong>$1</strong>');
      let highlightedBreed = pet.breed.replace(regex, '<strong>$1</strong>');
      let highlightedColor = pet.color.replace(regex, '<strong>$1</strong>');
      let highlightedComment = pet.comment.replace(regex, '<strong>$1</strong>');
      let highlightedAddress = pet.address ? pet.address.replace(regex, '<strong>$1</strong>') : '';

      // Render matched suggestions
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
        this.onResultSelect(pet.id);
      });

      // Add the suggestion to the dropdown result of live search so it appears on the result lists.
      this.resultsContainer.appendChild(item);
    });

    let viewAll = document.createElement('div');
    viewAll.className = 'search-view-all';
    viewAll.textContent = 'View all results for "' + query + '"';

    // When user clicks on view all result input items, it tells mediator app to fetch all paginated search result.
    viewAll.addEventListener('click', () => {
      this.onViewAll(query);
    });

    // Add the view all button at the bottom of the suggestions dropdown lists.
    this.resultsContainer.appendChild(viewAll);
  }

  /**
   * Renders the full search results with pagination.
   */
  renderFullResults(data, query) {
    this.resultsContainer.innerHTML = '';

    if (data.results.length === 0) {
      this.resultsContainer.innerHTML = '<div class="search-no-results">No results found</div>';
      return;
    }

    // Loop through the search result, create the html card with the pet details
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

      // Click handler which tells the mediator app the user selected a pet.
      item.addEventListener('click', () => {
        this.onResultClick(pet);
      });

      // Add each card to the results container that is matched.
      this.resultsContainer.appendChild(item);
    });

    // Pagination — show "Load more" if there are more results
    if (data.page * data.limit < data.total) {
      let loadMore = document.createElement('div');
      loadMore.className = 'search-load-more';
      loadMore.textContent = 'Load more results (' + data.total + ' total)';
      loadMore.addEventListener('click', () => {
        this.currentPage++;
        this.onViewAll(query);
      });

      // Add loadmore button to the live search feature.
      this.resultsContainer.appendChild(loadMore);
    }
  }

  /**
   * The next four methods are empty by default and overridden by the mediator app.
   * The mediator assigns its own logic to each event so that the UI class does not
   * need to know about AJAX or validation which helps us maintain many to one relationship
   * for the Mediator Design pattern.
   */
  onSearchInput(rawQuery) {}

  onResultSelect(petId) {}

  onViewAll(query) {}

  onResultClick(petData) {}
}