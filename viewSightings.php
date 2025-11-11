<?php
// Start the cookie session for logged-in user
session_start();

// Inheritance model classes to implement different functions and db connections
require_once('Models/Database.php');
require_once('Models/LocationDataSets.php');
require_once('Models/ViewSightingsDataSets.php');

$view = new stdClass();

/**
 * Highlights search terms in a given text string.
 * If input matches any results, highlight it in yellow and bold it (custom CSS)
 */
function highlightSearchTerm($text, $searchTerm) {
 // If there's no search term or text, just return it as is
 if (empty($searchTerm) || empty($text)) {
  return htmlspecialchars($text);
 }

 // Escape special regex characters so they don’t break the regex pattern
 $searchTerm = preg_quote($searchTerm, '/');

 // Replace matched text with a highlighted version and add custom css
 $highlighted = preg_replace_callback(
  '/(' . $searchTerm . ')/i',
  function ($match) {
   // Wrap matching word in <mark> for highlighting
   return '<mark class="search-highlight">' .
   htmlspecialchars($match[0]) . '</mark>';
  },
  $text
 );

 // Return highlighted version or safe user input text if nothing matches
 return $highlighted ?? htmlspecialchars($text);
}

// Set how many results to show per page
$limit = 10;

// Get the current page number from the URL (default to 1)
$page = filter_input(
 INPUT_GET, 'page', FILTER_VALIDATE_INT,
 ['options' => ['default' => 1, 'min_range' => 1]]
);
// Calculate how many records to skip (used for pagination)
$offset = ($page - 1) * $limit;

// Get the search term from the URL if available
$searchTerm = trim($_GET['search'] ?? '');
$view->searchTerm = htmlspecialchars($searchTerm);

// Get filter and sort values from the URL (species, status, order, date)
$filterStatus = trim($_GET['filter_status'] ?? '');
$filterSpecies = trim($_GET['filter_species'] ?? '');
$sortOrder = trim($_GET['sort_order'] ?? '');
$sortDate = trim($_GET['sort_date'] ?? '');

// Save filters so form inputs can stay filled after submission
$view->filters = [
 'status' => $filterStatus,
 'species' => $filterSpecies
];

// Figure out which sorting is used
if (!empty($sortDate)) {
 $view->sort = $sortDate;
} elseif (!empty($sortOrder)) {
 $view->sort = $sortOrder;
} else {
 $view->sort = '';
}

// Create model object to handle database operation
$sightingsDataSet = new ViewSightingsDataSet();

// Combine all filter and sort settings into one array
$params = [
 'search' => $searchTerm,
 'status' => $filterStatus,
 'species' => $filterSpecies,
 'sort_order' => $sortOrder,
 'sort_date' => $sortDate
];

// Get total records for pagination that matched filters
$totalRecords = $sightingsDataSet->countSightingsWithFilters($params);
$totalPages = ceil($totalRecords / $limit);

// Save pagination data for the view
$view->page = $page;
$view->totalPages = $totalPages;
$view->limit = $limit;

// Fetch paginated sightings data
$view->sightingsDataSet = $sightingsDataSet->fetchSightingsWithFilters(
 $params, $limit, $offset
);

// Create a message to show above results
// Example: 152 total result(s). Showing page 1 of 16.
if ($totalRecords == 0) {
 $view->dbMessage = !empty($searchTerm)
  ? "No results found matching '" . htmlspecialchars($searchTerm) . "'."
  : "No results found.";
} else {
 $view->dbMessage = "$totalRecords total result(s)";
 if (!empty($searchTerm)) {
  $view->dbMessage .= " found matching '" .
  htmlspecialchars($searchTerm) . "'";
 }
 if ($totalPages > 1) {
  $view->dbMessage .= ". Showing page $page of $totalPages.";
 }
}

// Include the view
require_once('Views/viewSightings.phtml');
