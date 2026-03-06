<?php
require_once('Database.php');
require_once('SightingsData.php');
require_once('LocationDataSets.php');

/**
 * Data access layer for retrieving and counting pet sightings.
 * Supports filtered, sorted, and paginated queries by joining
 * pets and sightings data and mapping results to SightingsData objects.
 */
class CreateSightings
{
    protected $_dbHandle;

    public function __construct()
    {
        $this->_dbHandle = Database::getInstance()->getdbConnection();
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
}