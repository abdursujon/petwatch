<?php
require_once('../../../Models/Database.php');
require_once('../../../Models/SightingsDataSets.php');

$sightings = new SightingsDataSets();
echo json_encode($sightings->fetchAllSightings());