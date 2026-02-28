<?php
require_once('../../Models/Database.php');
require_once('../../Models/SightingsDataSet.php');

$sightings = new SightingsDataSet();
echo json_encode($sightings->fetchAllSightings());