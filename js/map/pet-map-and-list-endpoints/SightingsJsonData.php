<?php
/**
 * Endpoint that returns all pet sighting details as JSON.
 * We create an instance of SightingsDataSets which has method
 * fetchAllSightings(). This method returns all pet details.
 * This details then used by the pet map and list UI components.
 */
require_once(__DIR__ . '/../../ValidateAjaxToken.php');
validateAjaxToken();
require_once(__DIR__ . '/../../../models/Database.php');
require_once(__DIR__ . '/../../../models/SightingsDataSets.php');
$sightings = new SightingsDataSets();
echo json_encode($sightings->fetchAllSightings());