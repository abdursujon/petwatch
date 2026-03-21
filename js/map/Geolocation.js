export class Geolocation {
  constructor() {
    this.lat = null;
    this.lng = null;
    this.watchId = null;
    this.pulseMarker = null;
    this.dotMarker = null;
  }

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

  // Live tracking — updates the blue dot as the user moves
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

  // Stop live tracking
  stopTracking() {
    if (this.watchId !== null) {
      navigator.geolocation.clearWatch(this.watchId);
      this.watchId = null;
    }
  }

  // Add/update the blue dot on the map at the user location
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
}