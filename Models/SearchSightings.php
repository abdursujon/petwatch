<?php
require_once('Database.php');

class SearchSightings
{
  protected $_dbHandle;

  public function __construct()
  {
    $this->_dbHandle = Database::getInstance()->getdbConnection();
  }

  /**
   * Search pets by name, breed, species, colour, or address.
   * Results are ranked: exact match first, starts with second, contains third.
   * @param string $query
   * @param string $species
   * @param string $status
   * @param int $limit
   * @param int $offset
   * @return array
   */
  public function search($query, $species = '', $status = '', $limit = 10, $offset = 0): array
  {
    $sql = "SELECT pets.*, locations.latitude, locations.longitude, locations.address,
                         sightings.comment
                  FROM pets
                  INNER JOIN locations ON pets.id = locations.pet_id
                  INNER JOIN sightings ON pets.id = sightings.pet_id
                  WHERE (pets.name LIKE :query
                      OR pets.breed LIKE :query2
                      OR pets.species LIKE :query3
                      OR pets.color LIKE :query4
                      OR locations.address LIKE :query5)";

    $params = [
      ':query' => "%$query%",
      ':query2' => "%$query%",
      ':query3' => "%$query%",
      ':query4' => "%$query%",
      ':query5' => "%$query%"
    ];

    if ($species !== '') {
      $sql .= " AND pets.species = :species";
      $params[':species'] = $species;
    }

    if ($status !== '') {
      $sql .= " AND pets.status = :status";
      $params[':status'] = $status;
    }

    $sql .= "ORDER BY
            CASE
                WHEN pets.name LIKE :exact THEN 1
                WHEN pets.name LIKE :starts THEN 2
                ELSE 3
            END,
            locations.timestamp DESC,
            pets.name ASC";

    $params[':exact'] = $query;
    $params[':starts'] = "$query%";

    // Get total count before pagination
    $countSql = "SELECT COUNT(*) as total FROM (" . $sql . ") as counted";
    $countStmt = $this->_dbHandle->prepare($countSql);
    $countStmt->execute($params);
    $total = $countStmt->fetch()['total'];

    // Apply pagination
    $sql .= " LIMIT :limit OFFSET :offset";
    $stmt = $this->_dbHandle->prepare($sql);

    foreach ($params as $key => $value) {
      $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    $results = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
      $results[] = [
        'id' => $row['id'],
        'name' => $row['name'],
        'species' => $row['species'],
        'breed' => $row['breed'],
        'color' => $row['color'],
        'photo_url' => $row['photo_url'],
        'status' => $row['status'],
        'latitude' => $row['latitude'],
        'longitude' => $row['longitude'],
        'address' => $row['address'],
        'comment' => $row['comment']
      ];
    }

    return [
      'results' => $results,
      'total' => (int)$total
    ];
  }

  /**
   * Fetch suggestions for autocomplete.
   * Returns only pet names and IDs matching the query.
   * @param string $query
   * @param int $limit
   * @return array
   */
  public function fetchSuggestions($query, $limit = 5): array
  {
    $sql = "SELECT DISTINCT pets.id, pets.name, pets.species, pets.breed, pets.color, pets.photo_url, sightings.comment, locations.address                                                                                            
          FROM pets                                                                                                                                                                                                               
          INNER JOIN sightings ON pets.id = sightings.pet_id
          INNER JOIN locations ON pets.id = locations.pet_id
          WHERE (pets.name LIKE :query
          OR pets.breed LIKE :query2
          OR pets.species LIKE :query3
          OR pets.color LIKE :query4
          OR sightings.comment LIKE :query5
          OR locations.address LIKE :query6)
          ORDER BY
          CASE
          WHEN pets.name LIKE :exact THEN 1
          WHEN pets.name LIKE :starts THEN 2
          ELSE 3
          END,
          locations.timestamp DESC
          LIMIT :limit";

    $stmt = $this->_dbHandle->prepare($sql);
    $stmt->bindValue(':query', "%$query%");
    $stmt->bindValue(':query2', "%$query%");
    $stmt->bindValue(':query3', "%$query%");
    $stmt->bindValue(':query4', "%$query%");
    $stmt->bindValue(':query5', "%$query%");
    $stmt->bindValue(':query6', "%$query%");
    $stmt->bindValue(':exact', $query);
    $stmt->bindValue(':starts', "$query%");
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  /**
   * Fetch a single pet's full details by ID.
   * @param int $petId
   * @return array|null
   */
  public function fetchPetById($petId): ?array
  {
    $sql = "SELECT pets.*, locations.latitude, locations.longitude, locations.address,
                       sightings.comment
                FROM pets
                INNER JOIN sightings ON pets.id = sightings.pet_id
                INNER JOIN locations ON locations.sighting_id = sightings.id
                WHERE pets.id = :id
                ORDER BY locations.timestamp DESC
                LIMIT 1";

    $stmt = $this->_dbHandle->prepare($sql);
    $stmt->bindValue(':id', $petId, PDO::PARAM_INT);
    $stmt->execute();

    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
  }
}