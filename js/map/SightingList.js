import {MapAndSightingDataValidation} from './MapAndSightingDataValidation.js';

export class SightingList {
  constructor(containerId, petMap, ajax) {
    this.container = document.getElementById(containerId);
    this.petMap = petMap;
    this.allData = [];
    this.displayedCount = 0;
    this.nextSizeOfBatchLoadedSightings = 20;
    this.ajax = ajax;
    this.initialiseFilters();
  }


  setSightingData(data) {
    this.allData = data;
    this.filteredData = [...data];
    this.applyFilters();
    this.initialiseInfiniteScroll();
  }

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

  // render all sightings to the page
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
          <p class="card-text mb-1">"${MapAndSightingDataValidation.escapeHTML(pet.comment)}"</p>
          <p class="card-text mt-auto"><small class="text-muted">Location: <span class="sighting-address">Loading...</span></small></p>
          ${isLoggedIn ? `
          <button class="btn btn-primary btn-sm py-2 w-75 add-sighting-btn mt-1 mb-4" style="font-size: 14px;" data-pet-id="${MapAndSightingDataValidation.escapeHTML(pet.id)}">Add A New Sighting</button>
          ` : '<p class="text-muted mb-4"><small>Log in to add a sighting</small></p>'}
      </div>
  `;

      col.appendChild(card)
      this.container.appendChild(col);

      card.addEventListener('click', (e) => {
        if (e.target.classList.contains('add-sighting-btn')) return;
        document.getElementById('map').scrollIntoView({behavior: 'smooth'});
        this.petMap.map.flyTo([pet.latitude, pet.longitude], 18);
        setTimeout(() => {
          this.petMap.markers.forEach(marker => {
            if (marker.petId == pet.id) {
              marker.openPopup();
            }
          });
        }, 800);
      });
      card.style.cursor = 'pointer';

      let addressSpan = card.querySelector('.sighting-address');
      addressSpan.textContent = pet.address || 'Unknown location';

      // Reuse sighting mode from PetMap.js class
      if (isLoggedIn) {
        let btn = card.querySelector('.add-sighting-btn');
        btn.addEventListener('click', () => {
          document.getElementById('map').scrollIntoView({behavior: 'smooth'});
          this.petMap.enterCreateSightingMode(pet.id);
        })
      }
    });
    this.displayedCount += batch.length;
  }

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