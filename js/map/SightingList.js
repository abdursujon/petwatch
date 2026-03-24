/**
 * This class is design to render pet sightings data as a scrollable list of lost pet cards.
 * The list are locoted below the map.
 * Supports filtering of the pet sighting list by name, date, and species.
 * Lists are loaded in batches of 20 via infinite scroll to avoid rendering all lists at the same time.
 * The list consists of "Creat Sighting" button which notifies the mediator app via
 * onCardClick() and onCreateSighting() callbacks.
 */
import {MapAndSightingDataValidation} from './MapAndSightingDataValidation.js';

export class SightingList {

  /**
   * Constructor of the class sets up the sighting list container, initialise filter listeners.
   * It also configures batch loading to render 20 sighting lists at a time.
   * @param containerId - HTML element id of the card list container.
   */
  constructor(containerId) {
    this.container = document.getElementById(containerId);
    this.allData = [];
    this.displayedCount = 0;
    this.nextSizeOfBatchLoadedSightings = 20;
    this.initialiseFilters();
  }


  /**
   * Stores sighting data from the mediator.
   * Copy the data for filtering, applies current filters.
   * Also enable infinite scroll pagination.
   * @param data
   */
  setSightingData(data) {
    this.allData = data;
    this.filteredData = [...data];
    this.applyFilters();
    this.initialiseInfiniteScroll();
  }


  // Listen for changes on species, name and date filter dropdowns to re-apply filters.
  initialiseFilters() {
    document.getElementById('filter-species').addEventListener('change', () => {
      this.applyFilters();
    });
    document.getElementById('filter-name').addEventListener('change', () => {
      this.applyFilters();
    });

    document.getElementById('filter-date').addEventListener('change', () => {
      this.applyFilters();
    });
  }


  // Apply the filter that user has selected on the front end.
  applyFilters() {
    let species = document.getElementById('filter-species').value;
    let nameSort = document.getElementById('filter-name').value;
    let dateSort = document.getElementById('filter-date').value;

    // sort by species
    this.filteredData = this.allData.filter(pet => {
      if (species === 'all') return true;
      return pet.species.toLowerCase() === species;
    });

    // Sort by name
    if (nameSort === 'a-z') {
      this.filteredData.sort((a, b) => a.name.localeCompare(b.name));
    } else if (nameSort === 'z-a') {
      this.filteredData.sort((a, b) => b.name.localeCompare(a.name));
    }

    // Sort by date
    if (dateSort === 'newest') {
      this.filteredData.sort((a, b) => new Date(b.timestamp) - new Date(a.timestamp));
    } else if (dateSort === 'oldest') {
      this.filteredData.sort((a, b) => new Date(a.timestamp) - new Date(b.timestamp));
    }

    // Reset the list and re-render the pet sightings
    this.container.innerHTML = '';
    this.displayedCount = 0;
    this.renderCards();
  }


  /**
   * Render the next batch of 20 sighting cards from the filtered data.
   * Each card shows pet photo, name, sighting comment, breed, and address.
   * Also enable user to click the "Add A New Sighting" button when logged in.
   * If the new sighting button is clicked, the method notifies mediator app via
   * onCreateSighting() callback.
   */
  renderCards() {
    let batch = this.filteredData.slice(this.displayedCount, this.displayedCount + this.nextSizeOfBatchLoadedSightings);

    batch.forEach((pet, index) => {
      let col = document.createElement('div');
      col.className = 'col-12 col-md-6 col-lg-4 col-xxl-3 mb-5';
      let card = document.createElement('div');
      card.className = 'card shadow-lg rounded-3 h-100 border-0'
      card.innerHTML = `
      <img src="${MapAndSightingDataValidation.escapeHTML(pet.photo_url)}" alt="${MapAndSightingDataValidation.escapeHTML(pet.name)}" class="card-img-top" style="height: 200px; object-fit: cover;" />
      <div class="card-body d-flex flex-column">
          <div class="d-flex justify-content-between align-items-start mb-2">
              <h5 class="card-title mb-0">${MapAndSightingDataValidation.escapeHTML(pet.name)}</h5>
              <span class="badge ${pet.status === 'lost' ? 'bg-danger' : 'bg-success'}">${MapAndSightingDataValidation.escapeHTML(pet.status)}</span>
          </div>
          <p class="card-text text-muted mb-1"><small>Breed: ${MapAndSightingDataValidation.escapeHTML(pet.breed)}</small></p>
          <p class="card-text mb-3 fst-italic bg-light rounded-2 p-2" style="border-left: 3px solid #198754;">"${MapAndSightingDataValidation.escapeHTML(pet.comment)}"</p>
          <p class="card-text mt-auto mt-5"><small class="text-muted"> 📍 Location: <span class="sighting-address">Loading...</span></small></p>
          ${isLoggedIn ? `
          <button class="btn btn-primary btn-sm py-1 w-75 add-sighting-btn mt-1 mb-4 text-start" style="font-size: 16px;" data-pet-id="${MapAndSightingDataValidation.escapeHTML(pet.id)}">Add A New Sighting</button>
          ` : '<p class="text-muted mb-4"><small>Log in to add a sighting</small></p>'}
      </div>
  `;

      col.appendChild(card)
      this.container.appendChild(col);

      card.addEventListener('click', (e) => {
        if (e.target.classList.contains('add-sighting-btn')) return;
        if (this.onCardClick) {
          this.onCardClick(pet);
        }
      });
      card.style.cursor = 'pointer';

      let addressSpan = card.querySelector('.sighting-address');
      addressSpan.textContent = pet.address || 'Unknown location';

      // Notify mediator central app to enter sighting mode for this pet.
      if (isLoggedIn) {
        let btn = card.querySelector('.add-sighting-btn');
        btn.addEventListener('click', () => {
          if (this.onCreateSighting) {
            this.onCreateSighting(pet.id);
          }
        })
      }
    });
    this.displayedCount += batch.length;
  }


  // Load the next 20 list of sightings when user is withing 200px of the bottom of the page.
  initialiseInfiniteScroll() {
    window.addEventListener('scroll', () => {
      if (window.innerHeight + window.scrollY >= document.body.offsetHeight - 200) {
        if (this.displayedCount < this.filteredData.length) {
          this.renderCards();
        }
      }
    })
  }
}