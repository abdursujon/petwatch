<?php
require_once(__DIR__ . '/../../ValidateAjaxToken.php');
validateAjaxToken();
require_once(__DIR__ . '/../../../models/Database.php');
require_once(__DIR__ . '/../../../models/SightingsDataSets.php');
$sightings = new SightingsDataSets();
echo json_encode($sightings->fetchAllSightings());