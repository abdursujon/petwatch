<?php
session_start();
require_once('Models/SightingsDataSets.php');
$view = new stdClass();
$view->title = "view-sightings";
$sightingsDataSet = new SightingsDataSets();


/**
 * Handles Semester two sightings pagination when scroll it loads more data
 *
 */
require_once('Views/viewSightings.phtml');
