<?php
require_once(__DIR__ . '/../../ValidateAjaxToken.php');
validateAjaxToken();
require_once(__DIR__ . '/../../../Models/Database.php');
require_once(__DIR__ . '/../../../Models/SearchSightings.php');;
header('Content-Type: application/json');

$query = trim($_GET['q'] ?? '');
$species = trim($_GET['species'] ?? '');
$status = trim($_GET['status'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = min(20, max(1, (int)($_GET['limit'] ?? 10)));
$offset = ($page - 1) * $limit;

if (strlen($query) < 2) {
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