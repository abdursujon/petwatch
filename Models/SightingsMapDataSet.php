<?php
require_once('Database.php');
require_once('ViewSightingsData.php');

/**
 * Data access layer for retrieving and counting pet sightings.
 * Supports filtered, sorted, and paginated queries by joining
 * pets and sightings data and mapping results to SightingsData objects.
 */
class SightingsMapDataSet {
    protected $_dbHandle;

    public function __construct() {
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
     * @return void
     */
    public function fetchAllSightings(){
        $sqlQuery = "SELECT pets.*, locations.latitude, locations.longitude, locations.timestamp, 
                            sightings.comment, sightings.user_id, sightings.pet_id
             FROM pets
             INNER JOIN locations ON pets.id = locations.pet_id
             INNER JOIN sightings ON pets.id = sightings.pet_id";

        $statement = $this->_dbHandle->prepare($sqlQuery);
        $statement -> execute();

        $dataSet = [];
        while($row = $statement->fetch()){
            $dataSet[] = [
                'id'         => $row['id'],
                'name'       => $row['name'],
                'species'    => $row['species'],
                'breed'      => $row['breed'],
                'color'      => $row['color'],
                'photo_url'  => $row['photo_url'],
                'status'     => $row['status'],
                'latitude'   => $row['latitude'],
                'longitude'  => $row['longitude'],
                'timestamp'  => $row['timestamp'],
                'comment'    => $row['comment']
            ];
        }
        echo json_encode($dataSet);
    }
}