<?php
require_once('Database.php');
require_once('ViewSightingsData.php');

/**
 * Data access layer for retrieving and counting pet sightings.
 * Supports filtered, sorted, and paginated queries by joining
 * pets and sightings data and mapping results to SightingsData objects.
 */
class ViewSightingsDataSet {
    protected $_dbHandle, $_dbInstance;

    public function __construct() {
        $this->_dbInstance = Database::getInstance();
        $this->_dbHandle =
            $this->_dbInstance->getdbConnection();
    }

    public function fetchSightingsWithFilters(
        array $params, int $limit, int $offset
    ): array {
        $sqlQuery = "SELECT p.name, p.species, p.status,
                     p.photo_url, s.comment, s.latitude,
                     s.longitude, s.timestamp
                     FROM sightings s
                     INNER JOIN pets p ON s.pet_id = p.id
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

        $sqlQuery .= " LIMIT :limit OFFSET :offset";

        $statement = $this->_dbHandle->prepare($sqlQuery);

        foreach ($bindParams as $key => $value) {
            $statement->bindValue($key, $value);
        }

        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->bindValue(':offset', $offset, PDO::PARAM_INT);

        $statement->execute();

        $dataSet = [];
        while ($row = $statement->fetch()) {
            $dataSet[] = new SightingsData($row);
        }

        return $dataSet;
    }

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
