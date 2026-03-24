<?php
require_once('Database.php');
require_once('SightingsData.php');
require_once('SightingsDataSets.php');
require_once('LocationDataSets.php');

/**
 * This class designed to handle CRUD operation on pet sightings data.
 */
class CreateSightings
{
  protected $_dbHandle;

  public function __construct()
  {
    $this->_dbHandle = Database::getInstance()->getdbConnection();
  }

  /**
   * recordSighting method insertss a new sighting and it's location into database.
   */
  public function recordSighting($petId, $userId, $comment, $latitude, $longitude, $address): bool
  {
    try {
      $sql = "INSERT INTO sightings
                    (pet_id, user_id, comment)
                    VALUES
                    (:pet_id, :user_id, :comment)";
      $stmt = $this->_dbHandle->prepare($sql);
      $success = $stmt->execute([
        ':pet_id' => $petId,
        ':user_id' => $userId,
        ':comment' => $comment
      ]);

      if ($success) {
        $sightingId = $this->_dbHandle->lastInsertId();
        $locationDataSet = new LocationDataSet();
        $timestamp = date('Y-m-d H:i:s');
        $locationDataSet->insertLocation($petId, $latitude, $longitude, $timestamp, $address, $sightingId);
      }

      return $success;
    } catch (PDOException $e) {
      throw new Exception("Error saving sighting: " . $e->getMessage());
    }
  }


  /**
   * Get all lost pet information from the database.
   */
  public function getAllLostPets(): array
  {
    $sql = "SELECT id, name, species, photo_url FROM pets WHERE status = 'lost' ORDER BY name ASC";
    $stmt = $this->_dbHandle->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }


  /**
   * Get all sightings by a specific user id.
   */
  public function getSightingsByUser($userId): array
  {
    try {
      $sql = "SELECT
                        s.id,
                        p.name AS pet_name,
                        p.species,
                        p.photo_url,
                        s.comment,
                        l.latitude,
                        l.longitude,
                        l.timestamp
                    FROM sightings s
                    JOIN pets p ON s.pet_id = p.id
                    JOIN locations l ON l.sighting_id = s.id
                    WHERE s.user_id = :user_id
                    ORDER BY l.timestamp DESC";
      $stmt = $this->_dbHandle->prepare($sql);
      $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
      $stmt->execute();
      return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      throw new Exception("Error fetching user sightings: " . $e->getMessage());
    }
  }

  /**
   * Update sighting when user id match.
   */
  public function updateSighting($sightingId, $userId, $data): bool
  {
    try {
      // Update comment on sightings table
      $sql = "UPDATE sightings SET comment = :comment                                                                                                                                                                                 
          WHERE id = :id AND user_id = :user_id";
      $stmt = $this->_dbHandle->prepare($sql);
      $stmt->execute([
        ':comment' => $data['comment'] ?? '',
        ':id' => $sightingId,
        ':user_id' => $userId
      ]);

      // Update location in locations table
      $latitude = $data['latitude'] ?? null;
      $longitude = $data['longitude'] ?? null;

      if ($latitude && $longitude) {
        $locSql = "UPDATE locations
                 SET latitude = :latitude, longitude = :longitude, timestamp = :timestamp
                 WHERE sighting_id = :sighting_id";
        $locStmt = $this->_dbHandle->prepare($locSql);
        $locStmt->execute([
          ':latitude' => $latitude,
          ':longitude' => $longitude,
          ':timestamp' => date('Y-m-d H:i:s'),
          ':sighting_id' => $sightingId
        ]);
      }
      return true;

    } catch (PDOException $e) {
      throw new Exception("Error updating sighting: " . $e->getMessage());
    }
  }


  /**
   * Delete sightings by user id that has requested delete.
   */
  public function deleteSighting($sightingId, $userId): bool
  {
    try {
      $petStmt = $this->_dbHandle->prepare(
        "SELECT pet_id FROM sightings
               WHERE id = :id AND user_id = :user_id"
      );
      $petStmt->execute([
        ':id' => $sightingId,
        ':user_id' => $userId
      ]);
      $petId = $petStmt->fetchColumn();

      if (!$petId) {
        return false;
      }

      $delLoc = $this->_dbHandle->prepare(
        "DELETE FROM locations WHERE sighting_id = :sighting_id"
      );
      $delLoc->execute([':sighting_id' => $sightingId]);

      $sql = "DELETE FROM sightings
                  WHERE id = :id AND user_id = :user_id";
      $stmt = $this->_dbHandle->prepare($sql);
      $stmt->bindValue(':id', $sightingId, PDO::PARAM_INT);
      $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
      $stmt->execute();

      return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
      throw new Exception("Error deleting sighting: " . $e->getMessage());
    }
  }

}