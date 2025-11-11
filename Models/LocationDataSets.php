<?php
// Inheritance methods and database connections from below class models
require_once('Database.php');
require_once('LocationData.php');

/**
 * Class LocationDataSet
 * Handles syncing and inserting pet location data
 * from the sightings table into the location table.
 */
class LocationDataSet {
 /**
  * @var PDO
  */
 private $_dbHandle;

 /**
  * Constructor to inheritance database connection
  */
 public function __construct() {
  $this->_dbHandle = Database::getInstance()->getdbConnection();
 }

 /**
  * Insert a single new location record (used after new sighting is created).
  * Allows updating the location table when user creates new sightings.
  * @throws Exception
  */
 public function insertLocation(
  $petId, $latitude, $longitude, $timestamp = null
 ): bool {
  try {
   // Insert location data and upload current date/time when sighted
   // COALESCE defaults to current time if timestamp not given
   $sql = "INSERT INTO locations
          (pet_id, latitude, longitude, timestamp)
          VALUES
          (:pet_id, :latitude, :longitude, :timestamp)";
   $stmt = $this->_dbHandle->prepare($sql);
   return $stmt->execute([
    ':pet_id' => $petId,
    ':latitude' => $latitude,
    ':longitude' => $longitude,
    ':timestamp' => $timestamp
   ]);
  } catch (PDOException $e) {
   throw new Exception(
    "Error inserting location: " . $e->getMessage()
   );
  }
 }
}
