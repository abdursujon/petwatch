<?php

/**
 * Data model representing a single location record.
 * Encapsulates location attributes associated with a pet sighting
 * and provides read-only access to persisted location data.
 */
class LocationData {
    protected $_id, $_pet_id, $_latitude, $_longitude, $_timestamp;

    public function __construct($dbRow) {
        $this->_id = $dbRow['id'];
        $this->_pet_id = $dbRow['pet_id'];
        $this->_latitude = $dbRow['latitude'];
        $this->_longitude = $dbRow['longitude'];
        $this->_timestamp = $dbRow['timestamp'];
    }

    public function getLocationId() {
        return $this->_id;
    }

    public function getPetId() {
        return $this->_pet_id;
    }

    public function getLatitude() {
        return $this->_latitude;
    }

    public function getLongitude() {
        return $this->_longitude;
    }

    public function getTimestamp() {
        return $this->_timestamp;
    }
}
