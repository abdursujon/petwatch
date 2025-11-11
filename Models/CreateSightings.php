<?php
// Class Model required to inheritance database connections
// and posting location data to the corresponding table
require_once('Database.php');
require_once('LocationDataSets.php');

/**
 * CreateSightings class designed to create, update and delete sightings
 */
class CreateSightings {
 /**
  * @var PDO
  */
 private $_dbHandle;

 /**
  * Constructor to inheritance the database connections
  */
 public function __construct() {
  $this->_dbHandle = Database::getInstance()->getdbConnection();
 }

 /**
  * @return array return and array of lost pets
  * @throws Exception
  * Get all pets that are currently lost to use for dropdown list
  */
 public function getLostPets(): array {
  try {
   // Only get lost pets
   $sql = "SELECT id, name, species, breed, photo_url
           FROM pets
           WHERE status = 'lost'
           ORDER BY name ASC";
   $stmt = $this->_dbHandle->query($sql);
   // Return all lost pets
   return $stmt->fetchAll(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
   throw new Exception(
    'Database error fetching lost pets: ' . $e->getMessage()
   );
  }
 }

 /**
  * Method to record a new sighting in the database table sightings.
  * Also updates the locations table by connecting with LocationDataSet.
  * @throws Exception
  */
 public function recordSighting(
  $petId, $userId, $comment, $latitude, $longitude
 ): bool {
  try {
   // Insert the sightings data to the database
   $sql = "INSERT INTO sightings
          (pet_id, user_id, comment, latitude, longitude)
          VALUES
          (:pet_id, :user_id, :comment, :latitude, :longitude)";
   $stmt = $this->_dbHandle->prepare($sql);
   $success = $stmt->execute([
    ':pet_id' => $petId,
    ':user_id' => $userId,
    ':comment' => $comment,
    ':latitude' => $latitude,
    ':longitude' => $longitude
   ]);

   // Also insert new location record when a sighting is created
   if ($success) {
    $locationDataSet = new LocationDataSet();
    $timestamp = date('Y-m-d H:i:s');
    $locationDataSet->insertLocation(
     $petId, $latitude, $longitude, $timestamp
    );
   }

   return $success;
  } catch (PDOException $e) {
   throw new Exception("Error saving sighting: " . $e->getMessage());
  }
 }

 /**
  * @throws Exception
  * Get all sightings created by a specific user (with pet image).
  */
 public function getSightingsByUser($userId): array {
  try {
   // Join the table sightings and pets to get information
   $sql = "SELECT 
           s.id,
           p.name AS pet_name,
           p.species,
           p.photo_url,
           s.comment,
           p.photo_url AS photo_url,
           s.latitude,
           s.longitude,
           s.timestamp
           FROM sightings s
           JOIN pets p ON s.pet_id = p.id
           WHERE s.user_id = :user_id
           ORDER BY s.timestamp DESC";
   $stmt = $this->_dbHandle->prepare($sql);
   $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
   $stmt->execute();
   return $stmt->fetchAll(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
   throw new Exception(
    "Error fetching user sightings: " . $e->getMessage()
   );
  }
 }

 /**
  * @throws Exception
  * Allow owner and user to update sightings and corresponding location.
  */
 public function updateSighting($sightingId, $userId, $data): bool {
  try {
   $allowed = ['comment', 'latitude', 'longitude'];
   $fields = [];
   $params = [
    ':id' => $sightingId,
    ':user_id' => $userId
   ];

   // Prepare valid fields and parameters for SQL update
   foreach ($data as $key => $value) {
    if (in_array($key, $allowed, true)) {
     $fields[] = "$key = :$key";
     $params[":$key"] = $value;
    }
   }

   // Nothing to update
   if (empty($fields)) return false;

   // Update sightings table with new info and timestamp
   $sql = "UPDATE sightings 
          SET " . implode(', ', $fields) . ",
          timestamp = datetime('now')
          WHERE id = :id AND user_id = :user_id";
   $stmt = $this->_dbHandle->prepare($sql);
   $stmt->execute($params);

   if ($stmt->rowCount() === 0) return false;

   // Update or insert location to locations table
   $petStmt = $this->_dbHandle->prepare(
    "SELECT pet_id FROM sightings WHERE id = :id"
   );
   $petStmt->execute([':id' => $sightingId]);
   $petId = $petStmt->fetchColumn();

   if ($petId) {
    $timestamp = date('Y-m-d H:i:s');
    $latitude = $data['latitude'] ?? null;
    $longitude = $data['longitude'] ?? null;

    // Insert new location record
    $locSql = "INSERT INTO locations
              (pet_id, latitude, longitude, timestamp)
              VALUES
              (:pet_id, :latitude, :longitude, :timestamp)";
    $locStmt = $this->_dbHandle->prepare($locSql);
    $locStmt->execute([
     ':pet_id' => $petId,
     ':latitude' => $latitude,
     ':longitude' => $longitude,
     ':timestamp' => $timestamp
    ]);
   }

   return true;
  } catch (PDOException $e) {
   throw new Exception("Error updating sighting: " . $e->getMessage());
  }
 }

 /**
  * @throws Exception
  * Allow owner and user to delete their sightings by matching user id.
  */
 public function deleteSighting($sightingId, $userId): bool {
  try {
   // Get pet_id linked to this sighting
   $petStmt = $this->_dbHandle->prepare(
    "SELECT pet_id FROM sightings
     WHERE id = :id AND user_id = :user_id"
   );
   $petStmt->execute([
    ':id' => $sightingId,
    ':user_id' => $userId
   ]);
   $petId = $petStmt->fetchColumn();

   if (!$petId) return false;

   // Delete the sighting
   $sql = "DELETE FROM sightings
          WHERE id = :id AND user_id = :user_id";
   $stmt = $this->_dbHandle->prepare($sql);
   $stmt->bindValue(':id', $sightingId, PDO::PARAM_INT);
   $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
   $stmt->execute();

   // If deleted, remove corresponding location
   if ($stmt->rowCount() > 0) {
    $delLoc = $this->_dbHandle->prepare(
     "DELETE FROM locations WHERE pet_id = :pet_id"
    );
    $delLoc->execute([':pet_id' => $petId]);
    return true;
   }

   return false;
  } catch (PDOException $e) {
   throw new Exception("Error deleting sighting: " . $e->getMessage());
  }
 }
}
