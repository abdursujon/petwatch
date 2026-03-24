<?php
/**
 * The endpoint first validates the AJAX token, then returns search suggestions as JSON for the live search autocomplete.
 * <p>
 *  Suggestion query must be one valid character.
 *  An instance of SearchSightings is created, which calls its fetchSuggestions
 *  method to return matching suggestions.
 * </p>
 */
require_once(__DIR__ . '/../../ValidateAjaxToken.php');
validateAjaxToken();
require_once(__DIR__ . '/../../../models/Database.php');
require_once(__DIR__ . '/../../../models/SearchSightings.php');;
header('Content-Type: application/json');

$query = trim($_GET['q'] ?? '');
$species = trim($_GET['species'] ?? '');
$status = trim($_GET['status'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = min(20, max(1, (int)($_GET['limit'] ?? 10)));
$offset = ($page - 1) * $limit;

if (strlen($query) < 1) {
  echo json_encode(['results' => [], 'total' => 0, 'page' => $page, 'limit' => $limit]);
  exit();
}

$query = htmlspecialchars($query, ENT_QUOTES, 'UTF-8');

$search = new SearchSightings();
$data = $search->search($query, $species, $status, $limit, $offset);

echo json_encode([
  'results' => $data['results'],
  'total' => $data['total'],
  'page' => $page,
  'limit' => $limit
]);