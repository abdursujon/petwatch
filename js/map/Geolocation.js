export class Geolocation{
    constructor(){
        this.lat = null;
        this.lng = null;
    }

    locate(onSuccess, onError){
        if(!navigator.geolocation){
           // If geolocation is not allowed or browser does not support we call this onError() function in MediatorApp.js to default to manchester.
           onError();
           return;
        }

        navigator.geolocation.getCurrentPosition(
            (position) => {
                onSuccess(position.coords.latitude, position.coords.longitude);
            },
            () => {
                onError();
            }
        );
    }

    // Add a blue dot on the map at the user location
    showUserLocation(map, lat, lng){
        L.circleMarker([lat, lng], {
            radius: 8,
            fillColor: '#2196F3',
            weight:2,
            color:'#fff',
            fillOpacity: 1
        }).addTo(map)
          .bindPopup('You are here')
          .on('mouseover', event => {
              event.target.openPopup();
          })
          .on('mouseout', event => {
              event.target.closePopup();
          });
    }
}