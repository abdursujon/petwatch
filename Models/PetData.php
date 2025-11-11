<?php

// Class model works as data model for PetDataSets.php class
class PetData {
 /**
  * @var int $_id pet id
  * @var string $_name pet name
  * @var string $_species pet type
  * @var string $_breed breed of the pet
  * @var string $_color color of the pet
  * @var string $_description how the pet looks and its traits
  * @var string $_pet_images pet pictures
  * @var string $_status lost or found
  * @var string $_date_reported date pet was reported
  * @var int $_user_id the user who owns the pet
  */
 protected $_id, $_name, $_species, $_breed, $_color,
           $_description, $_pet_images, $_status,
           $_date_reported, $_user_id;

 // Constructor
 public function __construct($dbRow) {
  $this->_id = $dbRow['id'];
  $this->_name = $dbRow['name'];
  $this->_species = $dbRow['species'];
  $this->_breed = $dbRow['breed'];
  $this->_color = $dbRow['color'];
  $this->_description = $dbRow['description'];
  $this->_pet_images = $dbRow['photo_url'];
  $this->_status = $dbRow['status'];
  $this->_date_reported = $dbRow['date_reported'];
  $this->_user_id = $dbRow['user_id'];
 }

 // Get pet id
 public function getId() {
  return $this->_id;
 }

 // Get pet name
 public function getName() {
  return $this->_name;
 }

 // Get pet species (e.g. dog, cat, bird)
 public function getSpecies() {
  return $this->_species;
 }

 // Get the pet breed
 public function getBreed() {
  return $this->_breed;
 }

 // Get the pet color
 public function getColor() {
  return $this->_color;
 }

 // Get pet description
 public function getDescription() {
  return $this->_description;
 }

 // Get the pet image
 public function getPetImages() {
  return $this->_pet_images;
 }

 // Get the pet status (lost/found)
 public function getStatus() {
  return $this->_status;
 }

 // Get reported time that pet was lost
 public function getDateReported() {
  return $this->_date_reported;
 }

 // Get pet by user id
 public function getPetsByUser() {
  return $this->_user_id;
 }
}
