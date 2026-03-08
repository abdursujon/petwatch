<?php
require_once('Database.php');
require_once('SightingsData.php');
require_once('LocationDataSets.php');

/**
 * Data access layer for retrieving and counting pet sightings.
 * Supports filtered, sorted, and paginated queries by joining
 * pets and sightings data and mapping results to SightingsData objects.
 */
class SightingsDataSets
{
  protected $_dbHandle;

  public function __construct()
  {
    $this->_dbHandle = Database::getInstance()->getdbConnection();
  }

  /**
   * fetchAllSightings method returns json_encoded data for front end to use purpose.
   * <p>
   *     1. Join pets with location and sightings table to get required pets data for ajax endpoints
   *     2. Execute the query to get pet details and sightings.
   *     3. Create an array variable to store executed data in while loop.
   *     4. Echo the dataSet as json so front end can use the data.
   * </p>
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