<?php
session_start();
// Inheritance the model class CreateSightings.php to use its methods
require_once('Models/CreateSightings.php');

$view = new stdClass();
$view->title = "Create Sightings";
$view->successMessage = ''; // Empty variable initiated to show success
$view->errorMessage = ''; // variable to show error
$view->pets = []; // Initialise pets array to hold objects later
$view->sightings = []; // Initialise sightings array to hold object later
$view->selectedPetId = null; // Added to track selected pet

// Check if a user is not logged in
if (empty($_SESSION['user_id'])) {
 $view->errorMessage = "You must log in to create a sighting.";
 require_once('Views/login.phtml');
 exit();
}

// Take the user id of the logged-in user (cast it to an int to make sure
// user_id is always integer number)
$userId = (int)$_SESSION['user_id'];
// Creat an object of type CreateSightings class
$createSightingsModel = new CreateSightings();

// now on the view we use pets array to hold the lost pet.
try {
 // The function get lost pet is used to get the lost pets from the database.
 $view->pets = $createSightingsModel->getLostPets();
} catch (Exception $e) {
 $view->errorMessage = $e->getMessage();
}

/**
 * Check posted input from the createsightings.phtml and validate
 * against the criteria set below
 * Comment up to 100 characters
 * Latitude is float and -90 to 90
 * Longitude is float and -180 to 180
 */
function validateSightingData($input): array {
 // Trim input and escape HTML for security (prevents XSS)
 $comment = trim($input['comment'] ?? '');
 $lat = $input['latitude'] ?? null;
 $lng = $input['longitude'] ?? null;

 $errors = [];

 // Validate latitude and longitude (must be numeric)
 if (!is_numeric($lat) || !is_numeric($lng)) {
  $errors[] = "Latitude and longitude must be numeric.";
 } else {
  $lat = (float)$lat;
  $lng = (float)$lng;

  if ($lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
   $errors[] = "Invalid coordinates. Latitude must be between -90 and 90, "
   . "longitude between -180 and 180.";
  }
 }

 // Validate comment length only (allow all characters)
 if (mb_strlen($comment) > 200) {
  $errors[] = "Invalid comment. Must be up to 200 characters.";
 }

 // Escape HTML to prevent XSS when displaying later
 $comment = htmlspecialchars($comment, ENT_QUOTES, 'UTF-8');

 return [$comment, $lat, $lng, $errors];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
 // Check if the user selected any pet from the lost pet list
 // If user selected any pet, post the pet_id to the view to show pet image
 if (
  isset($_POST['pet_id']) &&
  // When submit, update, and delete is not set.
  !isset($_POST['submit_sighting']) &&
  !isset($_POST['update_sighting']) &&
  !isset($_POST['delete_sighting'])
 ) {
  // This will allow us to show the selected image on the view
  $view->selectedPetId = (int)$_POST['pet_id'];
 }

 // check if the action is submit sightings, if yes, add sightings to the db
 if (isset($_POST['submit_sighting'])) {
  // filter the pet id that has been selected and make sure its always int
  $petId = filter_input(
   INPUT_POST, 'pet_id', FILTER_SANITIZE_NUMBER_INT
  );

  // Use the validateSightingsData function to check if the user has given
  // valid data
  [$comment, $lat, $lng, $errors] = validateSightingData($_POST);

  // If there is any error such as invalid lat, long, post it the view
  if ($errors) {
   $view->errorMessage = implode(' ', $errors);
  }
  // No errors found, attempt to submit the sighting
  else {
   try {
    // Add the sightings to the database and show it to the view
    $successOrFail = $createSightingsModel->recordSighting(
     $petId, $userId, $comment, $lat, $lng
    );
    $view->successMessage = $successOrFail
     ? "Sighting added."
     : "Failed to add sightings";
   } catch (Exception $e) {
    $view->errorMessage = $e->getMessage();
   }
  }
 }

 // check if the action is update_sighting if it's not submit-sightings
 elseif (isset($_POST['update_sighting'])) {
  // cast the pet id from string to int, and update it to the sighting
  $id = (int)$_POST['update_sighting'];

  // Now we check weather the updated sightings pet details meet
  // the sightings data requirement
  [$comment, $lat, $lng, $errors] = validateSightingData(
   $_POST['sightings'][$id] ?? []
  );

  // If there is any error such as invalid lat, long, post it the view
  if ($errors) {
   $view->errorMessage = implode(' ', $errors);
  }
  // No error found, attempt to update the pet
  else {
   try {
    // Prepare the data array for the model
    $data = [
     'comment' => $comment,
     'latitude' => $lat,
     'longitude' => $lng
    ];

    // Update the sightings data in the database
    $successOrFail = $createSightingsModel->updateSighting(
     $id, $userId, $data
    );
    $view->successMessage = $successOrFail
     ? "Sighting updated."
     : "No changes made.";
   } catch (Exception $e) {
    $view->errorMessage = $e->getMessage();
   }
  }
 }

 // Delete a sighting from the database
 elseif (isset($_POST['delete_sighting'])) {
  $sightingId = (int)$_POST['delete_sighting'];

  // Use the deleteSighting function from the model class
  // and delete the requested sighting id
  try {
   $delete = $createSightingsModel->deleteSighting(
    $sightingId, $userId
   );
   $view->successMessage = $delete
    ? "Sighting deleted successfully."
    : "Failed to delete sighting.";
  } catch (Exception $e) {
   $view->errorMessage = $e->getMessage();
  }
 }
}

// Fetch user’s own sightings on the view when logged in and check error
try {
 // Get the sightings of a logged-in user
 $view->sightings = $createSightingsModel->getSightingsByUser(
  $userId
 );
} catch (Exception $e) {
 $view->errorMessage = $e->getMessage();
}

// Load the main view
require_once('Views/createSightings.phtml');
