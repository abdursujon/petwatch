<?php

/**
 * Data model representing a pet sighting record.
 * Encapsulates sighting-related attributes loaded from the database
 * and provides read-only access to pet and location details.
 */
class sightingsData {
    protected $_pet_name, $_pet_species, $_pet_status,
              $_pet_images, $_comment, $_latitude,
              $_longitude, $_timestamp;

    public function __construct($dbRow) {
        $this->_pet_name = $dbRow['name'];
        $this->_pet_species = $dbRow['species'];
        $this->_pet_status = $dbRow['status'];
        $this->_pet_images = $dbRow['photo_url'];
        $this->_comment = $dbRow['comment'];
        $this->_latitude = $dbRow['latitude'];
        $this->_longitude = $dbRow['longitude'];
        $this->_timestamp = $dbRow['timestamp'];
    }

    public function getPetName() {
        return $this->_pet_name;
    }

    public function getPetSpecies() {
        return $this->_pet_species;
    }

    public function getPetStatus() {
        return $this->_pet_status;
    }

    public function getPetImages() {
        return $this->_pet_images;
    }

    public function getSightingsComment() {
        return $this->_comment;
    }

    public function getSightingsLatitude() {
        return $this->_latitude;
    }

    public function getSightingsLongitude() {
        return $this->_longitude;
    }

    public function getSightingsTimestamp() {
        return $this->_timestamp;
    }
}
