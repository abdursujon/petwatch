export class SightingList{
    constructor(containerId, petMap, ajax){
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

    initialiseFilters(){
        document.getElementById('filter-species').addEventListener('change', () =>{
            this.applyFilters();
        });
        document.getElementById('filter-name').addEventListener('change', () => {
            this.applyFilters();
        });

        document.getElementById('filter-date').addEventListener('change', () => {
            this.applyFilters();
        });
    }

    applyFilters(){
        let species = document.getElementById('filter-species').value;
        let nameSort = document.getElementById('filter-name').value;
        let dateSort = document.getElementById('filter-date').value;

        // sort by species
        this.filteredData = this.allData.filter(pet => {
            if(species === 'all') return true;
            return pet.species.toLowerCase() === species;
        });

        // Sort by name
        if(nameSort === 'a-z'){
            this.filteredData.sort((a, b) => a.name.localeCompare(b.name));
        } else if(nameSort === 'z-a'){
            this.filteredData.sort((a, b) => b.name.localeCompare(a.name));
        }

        // Sort by date
        if(dateSort === 'newest'){
            this.filteredData.sort((a, b) => new Date(b.timestamp) - new Date(a.timestamp));
        } else if(dateSort === 'oldest') {
            this.filteredData.sort((a, b) => new Date(a.timestamp) - new Date(b.timestamp));
        }

        // Reset the list and re-render the pet sightings
        this.container.innerHTML = '';
        this.displayedCount = 0;
        this.renderCards();

    }

    // render all sightings to the page
    renderCards(){
        let batch = this.filteredData.slice(this.displayedCount, this.displayedCount + this.nextSizeOfBatchLoadedSightings);

        batch.forEach((pet, index) => {
            let col = document.createElement('div');
            col.className = 'col-12 col-md-6 col-lg-4 col-xxl-3 mb-3';
            let card = document.createElement('div');
            card.className = 'card shadow-sm rounded h-100'
            card.innerHTML = `
                  <img src="${pet.photo_url}" alt="${pet.name}" class="card-img-top" />
                  <div class="card-body">
                      <h5 class="card-title">${pet.name}</h5>
                      <span class="badge ${pet.status === 'lost' ? 'bg-danger' : 'bg-success'}">${pet.status}</span>
                      <p class="card-text mt-2">${pet.comment}</p>
                      <p class="card-text"><small class="text-muted">Location: <span class="sighting-address">Loading...</span></small></p>
                      ${isLoggedIn ? `
                      <button class="btn btn-primary btn-sm add-sighting-btn" data-pet-id="${pet.id}">Add A New Sighting</button>
                      ` : '<p class="text-muted">Log in to add a sighting</p>'}
                  </div>
            `;
            col.appendChild(card)
            this.container.appendChild(col);

            let addressSpan = card.querySelector('.sighting-address');
            addressSpan.textContent = pet.address || 'Unknown location';

            // Reuse sighting mode from PetMap.js class
            if(isLoggedIn){
                let btn = card.querySelector('.add-sighting-btn');
                btn.addEventListener('click', () => {
                    document.getElementById('map').scrollIntoView({behavior: 'smooth'});
                    this.petMap.enterCreateSightingMode(pet.id);
                })
            }
        });
        this.displayedCount += batch.length;
    }

    initialiseInfiniteScroll(){
        window.addEventListener('scroll', () => {
            if(window.innerHeight + window.scrollY >= document.body.offsetHeight - 200){
                if(this.displayedCount < this.filteredData.length){
                    this.renderCards();
                }
            }
        })
    }
}