<?php
require_once('../../Models/Database.php');
require_once('../../Models/SightingsMapDataSet.php');

$sightings = new SightingsMapDataSet();
$sightings->fetchAllSightings();

