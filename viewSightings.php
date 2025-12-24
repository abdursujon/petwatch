<?php
session_start();

require_once('Models/Database.php');
require_once('Models/LocationDataSets.php');
require_once('Models/ViewSightingsDataSets.php');

$view = new stdClass();

/**
 * Highlights matched search terms in result text.
 */
function highlightSearchTerm($text, $searchTerm) {
    if (empty($searchTerm) || empty($text)) {
        return htmlspecialchars($text);
    }

    $searchTerm = preg_quote($searchTerm, '/');

    $highlighted = preg_replace_callback(
        '/(' . $searchTerm . ')/i',
        function ($match) {
            return '<mark class="search-highlight">' .
                htmlspecialchars($match[0]) . '</mark>';
        },
        $text
    );

    return $highlighted ?? htmlspecialchars($text);
}

$limit = 10;

$page = filter_input(
    INPUT_GET,
    'page',
    FILTER_VALIDATE_INT,
    ['options' => ['default' => 1, 'min_range' => 1]]
);
$offset = ($page - 1) * $limit;

$searchTerm = trim($_GET['search'] ?? '');
$view->searchTerm = htmlspecialchars($searchTerm);

$filterStatus = trim($_GET['filter_status'] ?? '');
$filterSpecies = trim($_GET['filter_species'] ?? '');
$sortOrder = trim($_GET['sort_order'] ?? '');
$sortDate = trim($_GET['sort_date'] ?? '');

$view->filters = [
    'status' => $filterStatus,
    'species' => $filterSpecies
];

$view->sort = $sortDate ?: $sortOrder ?: '';

$sightingsDataSet = new ViewSightingsDataSet();

$params = [
    'search' => $searchTerm,
    'status' => $filterStatus,
    'species' => $filterSpecies,
    'sort_order' => $sortOrder,
    'sort_date' => $sortDate
];

$totalRecords = $sightingsDataSet->countSightingsWithFilters($params);
$totalPages = ceil($totalRecords / $limit);

$view->page = $page;
$view->totalPages = $totalPages;
$view->limit = $limit;

$view->sightingsDataSet =
    $sightingsDataSet->fetchSightingsWithFilters(
        $params,
        $limit,
        $offset
    );

if ($totalRecords == 0) {
    $view->dbMessage = !empty($searchTerm)
        ? "No results found matching '" . htmlspecialchars($searchTerm) . "'."
        : "No results found.";
} else {
    $view->dbMessage = "$totalRecords total result(s)";
    if (!empty($searchTerm)) {
        $view->dbMessage .=
            " found matching '" . htmlspecialchars($searchTerm) . "'";
    }
    if ($totalPages > 1) {
        $view->dbMessage .= ". Showing page $page of $totalPages.";
    }
}

require_once('Views/viewSightings.phtml');
