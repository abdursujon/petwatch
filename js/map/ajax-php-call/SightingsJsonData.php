<?php
require_once(__DIR__ . '/../../ValidateAjaxToken.php');
validateAjaxToken();
require_once(__DIR__ . '/../../../Models/Database.php');
require_once(__DIR__ . '/../../../Models/SightingsDataSets.php');
$sightings = new SightingsDataSets();
echo json_encode($sightings->fetchAllSightings());