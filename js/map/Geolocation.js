/**
 * This class is design to support geolocation in browser.
 * We declare it as export since we implement Mediator Design,
 * where one app will be build consisting all sub-classes.
 * <p>
 *   The class provides one-time location lookup via locate() method.
 *   Continuous live GPS tracking via startTracking() methods.
 *   Also render a blue dot on the map by utilising method showLocation() when user allow geolocation.
 *   To stop live tracking we implement stopTracking() method.
 * </p>
 */
export class Geolocation {
  constructor() {
    this.lat = null;
    this.lng = null;
    this.watchId = null;
    this.pulseMarker = null;
    this.dotMarker = null;
  }


  /**
   * The method first check if the browser supports geolocation, if not show error.
   * If geolocation is supported, request the position with getCurrentPosition()
   * If successfull, passess the lat/lng to onSuccess()
   * On failure, calls onError()
   * @param onSuccess
   * @param onError
   */
  locate(onSuccess, onError) {
    if (!navigator.geolocation) {
      onError();
      return;
    }

    navigator.geolocation.getCurrentPosition(
      (position) => {
        onSuccess(position.coords.latitude, position.coords.longitude);
      },
      () => {
        onError();
      },
      {
        enableHighAccuracy: true,
        timeout: 10000,
        maximumAge: 0
      }
    );
  }


  /**
   * If geolocation is allowed, this method allow us to tracks the user location as they move.
   * watchPosition() fires for every time device detects a change in position.
   * Each update stores the new lat/lng, moves the blue dot on the map via showUserLocation().
   * The returned id is saved to this.watchId so stopTracking() method can cancel it later.
   * @param map
   * @param onUpdate
   */
  startTracking(map, onUpdate) {
    if (!navigator.geolocation) return;

    this.watchId = navigator.geolocation.watchPosition(
      (position) => {
        let lat = position.coords.latitude;
        let lng = position.coords.longitude;
        this.lat = lat;
        this.lng = lng;
        this.showUserLocation(map, lat, lng);
        if (onUpdate) onUpdate(lat, lng);
      },
      () => {
      },
      {
        enableHighAccuracy: true,
        timeout: 10000,
        maximumAge: 0
      }
    );
  }


  /**
   * Displays and updates the user's position on the map as blue dot with pulsing ring animation.
   * On initial call, it creats both markers and adds a hover popup.
   * The hover popup shows a message (Your location) when user interact with it.
   * On additional calls, it moves the existing geolocation marker to the new position without creating new one.
   * @param map
   * @param lat
   * @param lng
   */
  showUserLocation(map, lat, lng) {
    let latlng = [lat, lng];

    // If markers already exist, just move them
    if (this.pulseMarker && this.dotMarker) {
      this.pulseMarker.setLatLng(latlng);
      this.dotMarker.setLatLng(latlng);
      return;
    }

    // Outer pulsing ring
    this.pulseMarker = L.circleMarker(latlng, {
      radius: 16,
      fillColor: '#2196F3',
      fillOpacity: 0.15,
      color: '#2196F3',
      weight: 1,
      opacity: 0.3,
      className: 'user-pulse'
    }).addTo(map);

    // Inner solid blue dot
    this.dotMarker = L.circleMarker(latlng, {
      radius: 7,
      fillColor: '#2196F3',
      weight: 2,
      color: '#fff',
      fillOpacity: 1
    }).addTo(map)
      .bindPopup(`                                                                                                                                                                                                                                                                                                                                                                                                                                              
    <div style="text-align: center; padding: 4px 8px;">                                                                                                                                                                             
        <span style="font-weight: 600; color: #2196F3; font-size: 13px;">📍 Your Location</span>                                                                                                                                  
    </div>                                                                                                                                                                                                                          
    `, {closeButton: false, className: 'user-location-popup'})
      .on('mouseover', function (e) {
        e.target.openPopup();
      })
      .on('mouseout', function (e) {
        e.target.closePopup();
      });
  }


  /**
   * This method helps us stop the GPS tracking started by startTracking() method.
   * When user leave the page, or leave the site, this method help us stop tracking.
   * Which saves background processing and resources when we don't need the geolocation anymore.
   */
  stopTracking() {
    if (this.watchId !== null) {
      navigator.geolocation.clearWatch(this.watchId);
      this.watchId = null;
    }
  }
}