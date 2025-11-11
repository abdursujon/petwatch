<?php

/**
 * Class LocationData
 *
 * Works as a data model for LocationDataSet class
 */
class LocationData {
 /**
  * @var int $_id unique location id generated in db
  * @var string $_pet_id foreign key to get pet_id and sightings locations
  * @var string $_latitude sightings latitude
  * @var string $_longitude sightings longitude
  * @var string $_timestamp sighted time
  */
 protected $_id, $_pet_id, $_latitude, $_longitude, $_timestamp;

 /**
  * LocationData constructor.
  * Initializes the object with data from a database row.
  */
 public function __construct($dbRow) {
  $this->_id = $dbRow['id'];
  $this->_pet_id = $dbRow['pet_id'];
  $this->_latitude = $dbRow['latitude'];
  $this->_longitude = $dbRow['longitude'];
  $this->_timestamp = $dbRow['timestamp'];
 }

 // Get the location id
 public function getLocationId() {
  return $this->_id;
 }

 // Get the pet ID
 public function getPetId() {
  return $this->_pet_id;
 }

 // Get the location latitude
 public function getLatitude() {
  return $this->_latitude;
 }

 // Get the location longitude
 public function getLongitude() {
  return $this->_longitude;
 }

 // Get the sighted time when location was recorded
 public function getTimestamp() {
  return $this->_timestamp;
 }
}
