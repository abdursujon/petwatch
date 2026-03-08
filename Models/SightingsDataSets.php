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
                    JOIN locations l ON s.pet_id = l.pet_id
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

    public function updateSighting($sightingId, $userId, $data): bool
    {
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