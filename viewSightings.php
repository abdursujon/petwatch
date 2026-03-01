<?php
session_start();
require_once('Models/SightingsDataSet.php');
$view = new stdClass();
$view->title = "view-sightings";
$sightingsDataSet = new SightingsDataSet();

// Handle AJAX request from PetMap.js popup
if($_SERVER['REQUEST_METHOD'] === 'POST'
    && !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
    && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest'){
    header('Content-Type: application/json');

    $petId = filter_input(INPUT_POST, 'pet-id', FILTER_SANITIZE_NUMBER_INT);
    $comment = trim($_POST['sighting-comment'] ?? '');
    $lat = $_POST['latitude'] ?? null;
    $long = $_POST['longitude'] ?? null;

    [$comment, $lat, $long, $errors] = validateSightingsData([
        'comment'   => $comment,
        'latitude'  => $lat,
        'longitude' => $long
    ]);

    if ($errors){
        echo json_encode(['success' => false, 'error' => implode('', $errors)]);
        exit();
    }

    try{
        $success = $sightingsDataSet->recordSighting(
            $petId, $userId, $comment, $lat, $long
        );
        echo json_encode(['success'=> $success]);
    } catch (Exception $e){
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit();
}

/**
 * Handles Semester two sightings pagination when scroll it loads more data
 *
 */
require_once('Views/viewSightings.phtml');
