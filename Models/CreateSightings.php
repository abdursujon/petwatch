<?php
require_once('Database.php');
require_once('LocationDataSets.php');

/**
 * Handles creation, retrieval, updating, and deletion of pet sighting records.
 * Manages persistence of sightings and synchronizes related location data
 * using the locations dataset.
 */
class CreateSightings {
    private $_dbHandle;

    public function __construct() {
        $this->_dbHandle = Database::getInstance()->getdbConnection();
    }

    public function getLostPets(): array {
        try {
            $sql = "SELECT id, name, species, breed, photo_url
                    FROM pets
                    WHERE status = 'lost'
                    ORDER BY name ASC";
            $stmt = $this->_dbHandle->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception('Database error fetching lost pets: ' . $e->getMessage());
        }
    }

    public function recordSighting($petId, $userId, $comment, $latitude, $longitude): bool {
        try {
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

            if ($success) {
                $locationDataSet = new LocationDataSet();
                $timestamp = date('Y-m-d H:i:s');
                $locationDataSet->insertLocation($petId, $latitude, $longitude, $timestamp);
            }

            return $success;
        } catch (PDOException $e) {
            throw new Exception("Error saving sighting: " . $e->getMessage());
        }
    }

    public function getSightingsByUser($userId): array {
        try {
            $sql = "SELECT
                        s.id,
                        p.name AS pet_name,
                        p.species,
                        p.photo_url,
                        s.comment,
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
            throw new Exception("Error fetching user sightings: " . $e->getMessage());
        }
    }

    public function updateSighting($sightingId, $userId, $data): bool {
        try {
            $allowed = ['comment', 'latitude', 'longitude'];
            $fields = [];
            $params = [
                ':id' => $sightingId,
                ':user_id' => $userId
            ];

            foreach ($data as $key => $value) {
                if (in_array($key, $allowed, true)) {
                    $fields[] = "$key = :$key";
                    $params[":$key"] = $value;
                }
            }

            if (empty($fields)) {
                return false;
            }

            $sql = "UPDATE sightings
                    SET " . implode(', ', $fields) . ",
                        timestamp = NOW()
                    WHERE id = :id AND user_id = :user_id";
            $stmt = $this->_dbHandle->prepare($sql);
            $stmt->execute($params);

            if ($stmt->rowCount() === 0) {
                return false;
            }

            $petStmt = $this->_dbHandle->prepare(
                "SELECT pet_id FROM sightings WHERE id = :id"
            );
            $petStmt->execute([':id' => $sightingId]);
            $petId = $petStmt->fetchColumn();

            if ($petId) {
                $timestamp = date('Y-m-d H:i:s');
                $latitude = $data['latitude'] ?? null;
                $longitude = $data['longitude'] ?? null;

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

    public function deleteSighting($sightingId, $userId): bool {
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

            $sql = "DELETE FROM sightings
                    WHERE id = :id AND user_id = :user_id";
            $stmt = $this->_dbHandle->prepare($sql);
            $stmt->bindValue(':id', $sightingId, PDO::PARAM_INT);
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();

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
