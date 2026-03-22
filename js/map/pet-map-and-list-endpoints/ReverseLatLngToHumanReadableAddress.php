<?php
require_once(__DIR__ . '/../../ValidateAjaxToken.php');
validateAjaxToken();
session_start();

// Validate input
$lat = filter_input(INPUT_GET, 'lat', FILTER_VALIDATE_FLOAT);
$lng = filter_input(INPUT_GET, 'lng', FILTER_VALIDATE_FLOAT);

header('Content-Type: application/json');

if ($lat === false || $lng === false || $lat === null || $lng === null) {
  echo json_encode(['error' => 'Invalid coordinates']);
  exit();
}

// Check cache first — avoid hitting external API for same coordinates
$cacheDir = '../../cache/geocode/';
$cacheFile = $cacheDir . round($lat, 4) . '_' . round($lng, 4) . '.json';

if (file_exists($cacheFile)) {
  echo file_get_contents($cacheFile);
  exit();
}

// Call Nominatim API only if not cached
$url = 'https://nominatim.openstreetmap.org/reverse?format=json'
  . '&lat=' . urlencode($lat)
  . '&lon=' . urlencode($lng);

$options = [
  'http' => [
    'header' => "User-Agent: PetWatch/1.0\r\n"
  ]
];

$context = stream_context_create($options);
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

echo $response;
