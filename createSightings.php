<?php
session_start();
require_once('Models/CreateSightings.php');

$view = new stdClass();
$view->title = "Create Sightings";
$view->successMessage = '';
$view->errorMessage = '';
$view->pets = [];
$view->sightings = [];
$view->selectedPetId = null;

if (empty($_SESSION['user_id'])) {
    $view->errorMessage = "You must log in to create a sighting.";
    require_once('Views/login.phtml');
    exit();
}

$userId = (int)$_SESSION['user_id'];
$createSightingsModel = new CreateSightings();

try {
    $view->pets = $createSightingsModel->getLostPets();
} catch (Exception $e) {
    $view->errorMessage = $e->getMessage();
}

/**
 * Validates and sanitizes sighting input.
 * Returns sanitized values and validation errors.
 */
function validateSightingData($input): array {
    $comment = trim($input['comment'] ?? '');
    $lat = $input['latitude'] ?? null;
    $lng = $input['longitude'] ?? null;

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

    return [$comment, $lat, $lng, $errors];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (
        isset($_POST['pet_id']) &&
        !isset($_POST['submit_sighting']) &&
        !isset($_POST['update_sighting']) &&
        !isset($_POST['delete_sighting'])
    ) {
        $view->selectedPetId = (int)$_POST['pet_id'];
    }

    if (isset($_POST['submit_sighting'])) {
        $petId = filter_input(INPUT_POST, 'pet_id', FILTER_SANITIZE_NUMBER_INT);
        [$comment, $lat, $lng, $errors] = validateSightingData($_POST);

        if ($errors) {
            $view->errorMessage = implode(' ', $errors);
        } else {
            try {
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
    } elseif (isset($_POST['update_sighting'])) {
        $id = (int)$_POST['update_sighting'];
        [$comment, $lat, $lng, $errors] =
            validateSightingData($_POST['sightings'][$id] ?? []);

        if ($errors) {
            $view->errorMessage = implode(' ', $errors);
        } else {
            try {
                $data = [
                    'comment' => $comment,
                    'latitude' => $lat,
                    'longitude' => $lng
                ];

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
    } elseif (isset($_POST['delete_sighting'])) {
        $sightingId = (int)$_POST['delete_sighting'];

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

try {
    $view->sightings = $createSightingsModel->getSightingsByUser($userId);
} catch (Exception $e) {
    $view->errorMessage = $e->getMessage();
}

require_once('Views/createSightings.phtml');
