<?php
session_start();
require_once('Models/SightingsDataSet.php');
$view = new stdClass();
$view->title = "view-sightings";
$sightingsDataSet = new SightingsDataSet();


/**
 * Handles Semester two sightings pagination when scroll it loads more data
 *
 */
require_once('Views/viewSightings.phtml');
