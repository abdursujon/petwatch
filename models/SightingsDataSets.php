<?php
require_once('Database.php');
require_once('SightingsData.php');
require_once('LocationDataSets.php');

/**
 * This class is designed to support data access layer for retrieving and counting pet sightings.
 * It also handles filtered, sorting, and pagination queries by joining relevant table.
 */
class SightingsDataSets
{
  protected $_dbHandle;

  public function __construct()
  {
    $this->_dbHandle = Database::getInstance()->getdbConnection();
  }

  /**
   * fetchAllSightings method returns json_encoded data which we use in front end.
   * @return array
   */
  public function fetchAllSightings()
  {
    $sqlQuery = "SELECT pets.*, l.latitude, l.longitude, l.timestamp, l.address,                                                                                                                                                      
                      s.comment, s.user_id, s.pet_id                                                                                                                                                                                
                 FROM pets
                 INNER JOIN sightings s ON pets.id = s.pet_id                                                                                                                                                                         
                 INNER JOIN locations l ON l.sighting_id = s.id";

    $statement = $this->_dbHandle->prepare($sqlQuery);
    $statement->execute();

    $dataSet = [];
    while ($row = $statement->fetch()) {
      $dataSet[] = [
        'id' => $row['id'],
        'name' => $row['name'],
        'species' => $row['species'],
        'breed' => $row['breed'],
        'color' => $row['color'],
        'photo_url' => $row['photo_url'],
        'status' => $row['status'],
        'latitude' => $row['latitude'],
        'longitude' => $row['longitude'],
        'timestamp' => $row['timestamp'],
        'address' => $row['address'],
        'comment' => $row['comment']
      ];
    }
    return $dataSet;
  }
}