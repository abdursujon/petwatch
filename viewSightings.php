<?php
/**
 * This controller handles sightings view page.
 * The page laods all sightings lists and the pet map.
 */
session_start();
require_once('models/SightingsDataSets.php');
$view = new stdClass();
$view->title = "view-sightings";
$sightingsDataSet = new SightingsDataSets();

require_once('views/viewSightings.phtml');
