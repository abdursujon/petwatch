<?php

class PetData
{
  protected $_id, $_name, $_species, $_breed, $_color,
    $_description, $_pet_images, $_status,
    $_date_reported, $_user_id;

  public function __construct($dbRow)
  {
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

  public function getId()
  {
    return $this->_id;
  }

  public function getName()
  {
    return $this->_name;
  }

  public function getSpecies()
  {
    return $this->_species;
  }

  public function getBreed()
  {
    return $this->_breed;
  }

  public function getColor()
  {
    return $this->_color;
  }

  public function getDescription()
  {
    return $this->_description;
  }

  public function getPetImages()
  {
    return $this->_pet_images;
  }

  public function getStatus()
  {
    return $this->_status;
  }

  public function getDateReported()
  {
    return $this->_date_reported;
  }

  public function getPetsByUser()
  {
    return $this->_user_id;
  }
}
