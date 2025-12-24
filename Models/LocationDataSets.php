<?php
require_once('Database.php');
require_once('LocationData.php');

/**
 * Provides persistence logic for pet location records.
 * Responsible for inserting and synchronizing location data
 * derived from pet sightings into the locations table.
 */
class LocationDataSet {
    private $_dbHandle;

    public function __construct() {
        $this->_dbHandle = Database::getInstance()->getdbConnection();
    }

    public function insertLocation(
        $petId, $latitude, $longitude, $timestamp = null
    ): bool {
        try {
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
