<?php
require_once(__DIR__ . '/../../ValidateAjaxToken.php');
validateAjaxToken();
require_once(__DIR__ . '/../../../Models/Database.php');
require_once(__DIR__ . '/../../../Models/SearchSightings.php');
header('Content-Type: application/json');

$query = trim($_GET['q'] ?? '');

if (strlen($query) < 2) {
    echo json_encode([]);
    exit();
}

$query = htmlspecialchars($query, ENT_QUOTES, 'UTF-8');
$search = new SearchSightings();
echo json_encode($search->fetchSuggestions($query));
