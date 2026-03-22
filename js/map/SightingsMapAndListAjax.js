/**
 * SightingsMapAndListAjax handles all Ajax communication for the pet map feature.
 * 1. Endpoint one: fetchSightings() handles fetching all sightings data
 * 2. Endponit two: submitNewSighting() handles creating new sighting data
 * 3. Endpoint three:  reverseLatLngToHumanReadableAddress handles reversering location data to human readable location data object
 */
export class SightingsMapAndListAjax {
  constructor() {
    this.cachedSightings = null;
  }

  /**
   * Ajax endpoint one (GET)
   * fetchSightings() fetch all sightings data
   * Caches the result so PetMap.js and SightingsList.js can share the data without duplicating requests
   */
  fetchSightings(onSuccess, onError) {
    if (this.cachedSightings) {
      onSuccess(this.cachedSightings);
      return;

    }

    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'js/map/pet-map-and-list-endpoints/SightingsJsonData.php?token=' + ajaxToken, true);
    xhr.onreadystatechange = () => {
      if (xhr.readyState === 4) {
        if (xhr.status !== 200) {
          onError('Failed to load sightings. Status: ' + xhr.status);
          return;
        }
        try {
          let data = JSON.parse(xhr.responseText);
          this.cachedSightings = data;
          onSuccess(data);
        } catch (e) {
          onError('Invalid JSON response from server.')
        }
      }
    };
    xhr.send();
  }

  /**
   * Ajax endpoint two (POST)
   */
  submitNewSighting(petId, comment, lat, lng, address, onSuccess, onError) {
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'models/CreateSightings.php', true);
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

    xhr.onreadystatechange = () => {
      if (xhr.readyState === 4) {
        if (xhr.status !== 200) {
          onError('Server error. Status: ' + xhr.status);
          return;
        }
        try {
          let result = JSON.parse(xhr.responseText);
          if (result.success) {
            this.cachedSightings = null;
            onSuccess(result);
          } else {
            onError(result.error || 'Failed to add sighting.')
          }
        } catch (e) {
          onError('Unexpected server response.');
        }
      }
    };
    xhr.send(
      'pet_id=' + petId
      + '&sighting-comment=' + encodeURIComponent(comment)
      + '&latitude=' + lat
      + '&longitude=' + lng
      + '&address=' + encodeURIComponent(address || '')
      + '&token=' + ajaxToken
    );
  }

  /**
   * Ajax endpoint 3 (GET)
   *  reverseLatLngToHumanReadableAddress geocode lat/lng to human readable address.
   *  This ajax is called when user create a new sighting by choosing a location in the map by clicking or by choosing their own locatin.
   */
  reverseLatLngToHumanReadableAddress(lat, lng, onSuccess, onError) {
    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'js/map/live-search-endpoints/ReverseLatLngToHumanReadableAddress.php?lat=' + lat + '&lng=' + lng + '&token=' + ajaxToken, true);
    xhr.onreadystatechange = () => {
      if (xhr.readyState === 4) {
        if (xhr.status !== 200) {
          onError('Geocode request failed. Status: ' + xhr.status);
          return;
        }
        try {
          let data = JSON.parse(xhr.responseText);
          let address = data.address;
          let parts = [];
          if (address.road) {
            parts.push(address.road);
          } else if (address.neighbourhood) {
            parts.push(address.neighbourhood);
          }
          if (address.city) {
            parts.push(address.city);
          } else if (address.town) {
            parts.push(address.town);
          } else if (address.village) {
            parts.push(address.village);
          }
          if (address.postcode) {
            parts.push(address.postcode);
          }
          onSuccess(parts.join(',') || data.display_name);

        } catch (e) {
          onError('Invalid geocode response.')
        }
      }
    };
    xhr.send();
  }

}