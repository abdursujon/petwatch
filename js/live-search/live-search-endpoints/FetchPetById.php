<?php
/**
 * The endpoint first validates the AJAX token through calling the root script
 * This endpoint does GET which gets a single sighting as JSON by pet ID.
 */
require_once(__DIR__ . '/../../ValidateAjaxToken.php');
validateAjaxToken();
require_once(__DIR__ . '/../../../models/Database.php');
require_once(__DIR__ . '/../../../models/SearchSightings.php');

header('Content-Type: application/json');

$petId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$petId) {
  echo json_encode(['error' => 'Invalid pet ID']);
  exit();
}

$search = new SearchSightings();
$pet = $search->fetchPetById($petId);

if ($pet) {
  echo json_encode($pet);
} else {
  echo json_encode(['error' => 'Pet not found']);
}