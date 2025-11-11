<?php

/**
 * Class designed to handle data models for
 * ViewSightingsDataSets class.
 */
class sightingsData {
 /**
  * @var string $_pet_name Pet name.
  * @var string $_pet_species Pet species (dog, cat, etc.).
  * @var string $_pet_status Current status (lost/found).
  * @var string $_pet_images Pet images.
  * @var string $_comment Comment about the pet.
  * @var float $_latitude Sighting latitude.
  * @var float $_longitude Sighting longitude.
  * @var string $_timestamp Date and time recorded.
  */
 protected $_pet_name, $_pet_species, $_pet_status,
  $_pet_images, $_comment, $_latitude,
  $_longitude, $_timestamp;

 /**
  * Constructor to initialize sighting data
  * from a database row.
  */
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

 // Get pet name
 public function getPetName() {
  return $this->_pet_name;
 }

 // Get pet species
 public function getPetSpecies() {
  return $this->_pet_species;
 }

 // Get pet status (lost/found)
 public function getPetStatus() {
  return $this->_pet_status;
 }

 // Get pet images
 public function getPetImages() {
  return $this->_pet_images;
 }

 // Get sightings comment
 public function getSightingsComment() {
  return $this->_comment;
 }

 // Get latitude of the sighting
 public function getSightingsLatitude() {
  return $this->_latitude;
 }

 // Get longitude of the sighting
 public function getSightingsLongitude() {
  return $this->_longitude;
 }

 // Get sighting timestamp
 public function getSightingsTimestamp() {
  return $this->_timestamp;
 }
}
