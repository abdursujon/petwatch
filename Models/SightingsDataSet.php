<?php
require_once('Database.php');
require_once('ViewSightingsData.php');
require_once('LocationDataSets.php');

/**
 * Data access layer for retrieving and counting pet sightings.
 * Supports filtered, sorted, and paginated queries by joining
 * pets and sightings data and mapping results to SightingsData objects.
 */
class SightingsDataSet
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
        $sqlQuery = "SELECT pets.*, locations.latitude, locations.longitude, locations.timestamp, 
                            sightings.comment, sightings.user_id, sightings.pet_id
             FROM pets
             INNER JOIN locations ON pets.id = locations.pet_id
             INNER JOIN sightings ON pets.id = sightings.pet_id";

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
                'comment' => $row['comment']
            ];
        }
        return $dataSet;
    }

    /**
     * paginatedSightings() method reuse the data from fetchAllSightings() method to helps us build auto scrolled paginated list of all lost pets
     * @param $limit
     * @param $offset
     * @return array
     */
    public function paginatedSightings($limit, $offset)
    {
        $allSightings = $this->fetchAllSightings();
        return array_slice($allSightings, $offset, $limit);
    }

    /**
     * recordSighting in database
     * @param $petId
     * @param $userId
     * @param $comment
     * @param $latitude
     * @param $longitude
     * @return bool
     * @throws Exception
     */
    public function recordSighting($petId, $userId, $comment, $latitude, $longitude): bool
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
                $locationDataSet = new LocationDataSet();
                $timestamp = date('Y-m-d H:i:s');
                $locationDataSet->insertLocation($petId, $latitude, $longitude, $timestamp);
            }

            return $success;
        } catch (PDOException $e) {
            throw new Exception("Error saving sighting: " . $e->getMessage());
        }
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