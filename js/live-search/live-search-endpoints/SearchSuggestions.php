<?php
/**
 * The endpoint first validate the AJAX token
 * Next it returns search suggestions as JSON for the live search autocomplete.
 * Suggestion query must be one valid characters.
 * Afterwards, the query is validated again.
 * Next an instance of SearchSightings is created, which calls its fetchSuggestions
 * method to return matching suggestions.
 */
require_once(__DIR__ . '/../../ValidateAjaxToken.php');
validateAjaxToken();
require_once(__DIR__ . '/../../../models/Database.php');
require_once(__DIR__ . '/../../../models/SearchSightings.php');
header('Content-Type: application/json');

$query = trim($_GET['q'] ?? '');

if (strlen($query) < 1) {
  echo json_encode([]);
  exit();
}

$query = htmlspecialchars($query, ENT_QUOTES, 'UTF-8');
$search = new SearchSightings();
echo json_encode($search->fetchSuggestions($query));
