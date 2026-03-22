<?php
/**
 * This file is responsible for converting lat/long to human readable address.
 * We accomplish that through using Nominatim(OpenStreetMap API).
 * The result are cached to disk since nominatim has rate limits.
 *
 * <p>
 *   GET parameters: lat, lng
 *   The file also return JSON with address details or error message.
 * </p>
 */
require_once(__DIR__ . '/../../ValidateAjaxToken.php');
validateAjaxToken();
session_start();

// This handles deleting oldest cache since otherwise our cache will forever grow. We keep cache up to 1000.
$files = glob($cacheDir . '*.json');
if (count($files) > 1000) {
  usort($files, fn($a, $b) => filemtime($a) - filemtime($b));
  unlink($files[0]);
}

// Validate input
$lat = filter_input(INPUT_GET, 'lat', FILTER_VALIDATE_FLOAT);
$lng = filter_input(INPUT_GET, 'lng', FILTER_VALIDATE_FLOAT);

header('Content-Type: application/json');

if ($lat === false || $lng === false || $lat === null || $lng === null) {
  echo json_encode(['error' => 'Invalid coordinates']);
  exit();
}

// Check cache first — avoid hitting Nominatim API for same coordinates
$cacheDir = '../../cache/geocode/';
$cacheFile = $cacheDir . round($lat, 4) . '_' . round($lng, 4) . '.json';

// If coordinates has saved cache use those instead.
if (file_exists($cacheFile)) {
  echo file_get_contents($cacheFile);
  exit();
}

// Call Nominatim API only if location geocode is not cached since openstreet API has rate limits.
$url = 'https://nominatim.openstreetmap.org/reverse?format=json'
  . '&lat=' . urlencode($lat)
  . '&lon=' . urlencode($lng);

// We declare header agen name which open street requires to identify which website is requesting data.
// This header is a tag we choose. It can be any string but in this case developer named it as version 1.0
$options = [
  'http' => [
    'header' => "User-Agent: PetWatch/1.0\r\n"
  ]
];

// Create the context for the HTTP request required by Nominatim API.
$context = stream_context_create($options);

// Send GET request to Nominatim and store the JSON response.
$response = file_get_contents($url, false, $context);

if ($response === false) {
  echo json_encode(['error' => 'Geocode request failed']);
  exit();
}

// Cache the result for future requests
if (!is_dir($cacheDir)) {
  mkdir($cacheDir, 0755, true);
}
file_put_contents($cacheFile, $response);

// Send the response back to the browser where requested.
echo $response;
