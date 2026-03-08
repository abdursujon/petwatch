<?php
session_start();
require_once('js/ValidateAjaxToken.php');
require_once('Models/CreateSightings.php');

$view = new stdClass();
$view->title = "sightings";
$view->successMessage = '';
$view->errorMessage = '';

if (empty($_SESSION['user_id'])) {
  $view->errorMessage = "You must log in to create a sighting.";
  require_once('Views/login.phtml');
  exit();
}
$userId = (int)$_SESSION['user_id'];


$createSighting = new createSightings();

/**
 * Validates and sanitizes sighting input.
 * Returns sanitized values and validation errors.
 */
function validateSightingsData($input): array
{
  $comment = trim($input['comment'] ?? '');
  $lat = $input['latitude'] ?? null;
  $lng = $input['longitude'] ?? null;
  $address = $input['address'] ?? null;
  $errors = [];

  if (!is_numeric($lat) || !is_numeric($lng)) {
    $errors[] = "Latitude and longitude must be numeric.";
  } else {
    $lat = (float)$lat;
    $lng = (float)$lng;

    if ($lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
      $errors[] =
        "Invalid coordinates. Latitude must be between -90 and 90, longitude between -180 and 180.";
    }
  }

  if (mb_strlen($comment) > 200) {
    $errors[] = "Invalid comment. Must be up to 200 characters.";
  }
  $comment = htmlspecialchars($comment, ENT_QUOTES, 'UTF-8');

  return [$comment, $lat, $lng, $address, $errors];
}


// Handle AJAX request from PetMap.js popup
if ($_SERVER['REQUEST_METHOD'] === 'POST'
  && !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
  && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
  validateAjaxToken();
  header('Content-Type: application/json');

  $petId = filter_input(INPUT_POST, 'pet_id', FILTER_SANITIZE_NUMBER_INT);
  $comment = trim($_POST['sighting-comment'] ?? '');
  $lat = $_POST['latitude'] ?? null;
  $lng = $_POST['longitude'] ?? null;
  $address = trim($_POST['address'] ?? '');

  [$comment, $lat, $lng, $address, $errors] = validateSightingsData([
    'comment' => $comment,
    'latitude' => $lat,
    'longitude' => $lng,
    'address' => $address
  ]);

  if ($errors) {
    echo json_encode(['success' => false, 'error' => implode('', $errors)]);
    exit();
  }

  try {
    $success = $createSighting->recordSighting(
      $petId, $userId, $comment, $lat, $lng, $address
    );
    echo json_encode(['success' => $success]);
  } catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
  }
  exit();
}

/**
 * Handles form-based POST actions for sightings page.
 * Calls for right model based on the submit action by the user.
 * <p>
 *  submit_sighting: create a new sighting record with validated location data.
 *  update_sighting: modifies an existing sightings.
 *  delete_sighting: remove a sightings from the database.
 * </p>
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_POST['pet_id'])
    && !isset($_POST['submit_sighting'])
    && !isset($_POST['update_sighting'])
    && !isset($_POST['delete_sighting'])) {
    $view->selectedPetId = (int)$_POST['pet_id'];
  }
  try {
    if (isset($_POST['submit_sighting'])) {
      $petId = filter_input(INPUT_POST, 'pet_id', FILTER_SANITIZE_NUMBER_INT);
      [$comment, $lat, $lng, $address, $errors] = validateSightingsData($_POST);
      if ($errors) {
        $view->errorMessage = implode(' ', $errors);
      } else {
        $success = $createSighting->recordSighting($petId, $userId, $comment, $lat, $lng, $address);
        $view->successMessage = $success ? "Sighting added." : "Failed to add sighting.";
      }
    } elseif (isset($_POST['update_sighting'])) {
      $id = (int)$_POST['update_sighting'];
      [$comment, $lat, $lng, $errors] = validateSightingsData($_POST['sightings'][$id] ?? []);

      if ($errors) {
        $view->errorMessage = implode('', $errors);
      } else {
        $data = ['comment' => $comment, 'latitude' => $lat, 'longitude' => $lng];
        $success = $createSighting->updateSighting($id, $userId, $data);
        $view->successMessage = $success ? "Sighting updated." : "Failed to update sighting.";
      }
    } elseif (isset($_POST['delete_sighting'])) {
      $sightingId = (int)$_POST['delete_sighting'];
      $success = $createSighting->deleteSighting($sightingId, $userId);
      $view->successMessage = $success ? "Sighting deleted. " : "Failed to delete sighting, try again.";
    }
  } catch (Exception $e) {
    $view->errorMessage = $e->getMessage();
  }
}


$view->pets = $createSighting->getAllLostPets();
$view->sightings = $createSighting->getSightingsByUser($userId);
require_once('Views/createSightings.phtml');
