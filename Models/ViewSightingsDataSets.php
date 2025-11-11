<?php
// Inheritance database connection class and
// ViewSightings model
require_once('Database.php');
require_once('ViewSightingsData.php');

// Class to handle sightings data
class ViewSightingsDataSet {
 protected $_dbHandle, $_dbInstance;

 // Constructor to handle database inheritance
 public function __construct() {
  $this->_dbInstance = Database::getInstance();
  $this->_dbHandle =
   $this->_dbInstance->getdbConnection();
 }

 /**
  * Fetch sightings with search, filter,
  * sorting, and pagination.
  * @param array $params Keys: search, status,
  * species, sort_order, sort_date
  * @param int $limit Max records to return
  * @param int $offset Start index for records
  * @return array Array of SightingsData objects
  */
 public function fetchSightingsWithFilters(
  array $params, int $limit, int $offset
 ): array {
  /*
   * Query retrieves pet and sighting details.
   * INNER JOIN ensures only pets with
   * sightings are included.
   */
  $sqlQuery = "SELECT p.name, p.species, p.status,
               p.photo_url, s.comment, s.latitude,
               s.longitude, s.timestamp
               FROM sightings s
               INNER JOIN pets p ON s.pet_id = p.id
               WHERE 1=1";

  $bindParams = [];

  // Apply search filter
  if (!empty($params['search'])) {
   $sqlQuery .= " AND (p.name LIKE :search
                OR p.species LIKE :search
                OR p.status LIKE :search
                OR s.comment LIKE :search
                OR s.latitude LIKE :search
                OR s.longitude LIKE :search)";
   $bindParams[':search'] =
    '%' . $params['search'] . '%';
  }

  // Status filter
  if (!empty($params['status'])) {
   $sqlQuery .= " AND p.status = :status";
   $bindParams[':status'] = $params['status'];
  }

  // Species filter
  if (!empty($params['species'])) {
   $sqlQuery .= " AND p.species = :species";
   $bindParams[':species'] = $params['species'];
  }

  // Sort filter (date or name)
  if (!empty($params['sort_date'])) {
   $sqlQuery .= $params['sort_date'] === 'oldest'
    ? " ORDER BY s.timestamp ASC"
    : " ORDER BY s.timestamp DESC";
  } elseif (!empty($params['sort_order'])) {
   if ($params['sort_order'] === 'name_asc') {
    $sqlQuery .= " ORDER BY p.name ASC";
   } elseif ($params['sort_order'] === 'name_desc') {
    $sqlQuery .= " ORDER BY p.name DESC";
   } else {
    $sqlQuery .= " ORDER BY s.timestamp DESC";
   }
  } else {
   $sqlQuery .= " ORDER BY s.timestamp DESC";
  }

  // Pagination
  $sqlQuery .= " LIMIT :limit OFFSET :offset";

  $statement = $this->_dbHandle->prepare($sqlQuery);

  foreach ($bindParams as $key => $value) {
   $statement->bindValue($key, $value);
  }

  $statement->bindValue(':limit', $limit,
   PDO::PARAM_INT);
  $statement->bindValue(':offset', $offset,
   PDO::PARAM_INT);

  $statement->execute();

  // Convert results into objects
  $dataSet = [];
  while ($row = $statement->fetch()) {
   $dataSet[] = new SightingsData($row);
  }

  return $dataSet;
 }

 /**
  * Count sightings with dynamic filters.
  * @param array $params Keys: search, status,
  * species
  * @return int Total matched records
  */
 public function countSightingsWithFilters(
  array $params
 ): int {
  $sqlQuery = "SELECT COUNT(*)
               FROM pets p
               INNER JOIN sightings s
               ON p.id = s.pet_id
               WHERE 1=1";

  $bindParams = [];

  if (!empty($params['search'])) {
   $sqlQuery .= " AND (p.name LIKE :search
                OR p.species LIKE :search
                OR p.status LIKE :search
                OR s.comment LIKE :search
                OR s.latitude LIKE :search
                OR s.longitude LIKE :search)";
   $bindParams[':search'] =
    '%' . $params['search'] . '%';
  }

  if (!empty($params['status'])) {
   $sqlQuery .= " AND p.status = :status";
   $bindParams[':status'] = $params['status'];
  }

  if (!empty($params['species'])) {
   $sqlQuery .= " AND p.species = :species";
   $bindParams[':species'] = $params['species'];
  }

  $statement = $this->_dbHandle->prepare($sqlQuery);
  foreach ($bindParams as $key => $value) {
   $statement->bindValue($key, $value);
  }

  $statement->execute();
  return (int)$statement->fetchColumn(0);
 }
}
