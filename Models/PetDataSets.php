<?php
// Inheritance Database and PetData class
require_once('Database.php');
require_once('PetData.php');

// Class to establish connection between database and controller
// to get pets data
class PetDataSets {
 public $_dbHandle;
 protected $_dbInstance;

 // Inheritance database connection
 public function __construct() {
  $this->_dbInstance = Database::getInstance();
  $this->_dbHandle = $this->_dbInstance->getdbConnection();
 }

 // Get all pets belonging to a user
 public function getPetByUserID($user_id): array {
  // Select all pets for logged-in user by their user id
  // and show the newest first
  $sql = "SELECT * FROM pets
          WHERE user_id = :user_id
          ORDER BY date_reported DESC";
  $stmt = $this->_dbHandle->prepare($sql);
  // Bind user id value as integer
  $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
  $stmt->execute();

  // Fetch pets for specific logged-in user
  $pets = [];
  while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
   $pets[] = new PetData($row);
  }
  return $pets; // Return fetched pets
 }

 // Create a new pet entry. Status always defaults to 'lost'
 public function addPet(
  $user_id, $name, $species, $breed,
  $color, $description, $photo_url = null
 ) {
  // Insert new data to database when user adds a new pet
  // Status = lost, Date = current datetime
  $sql = "INSERT INTO pets
          (user_id, name, species, breed, color,
          description, photo_url, status, date_reported)
          VALUES
          (:user_id, :name, :species, :breed, :color,
          :description, :photo_url, 'lost', datetime('now'))";

  $stmt = $this->_dbHandle->prepare($sql);
  // Safely bind values
  $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
  $stmt->bindValue(':name', $name);
  $stmt->bindValue(':species', $species);
  $stmt->bindValue(':breed', $breed);
  $stmt->bindValue(':color', $color);
  $stmt->bindValue(':description', $description);
  $stmt->bindValue(':photo_url', $photo_url);
  $stmt->execute();

  // Return last inserted id for confirmation
  return $this->_dbHandle->lastInsertId();
 }

 // Update a pet entry (only owner can update details)
 public function updatePet($petId, $user_id, $data): bool {
  $fields = [];
  $params = [':id' => $petId, ':user_id' => $user_id];

  // Collect valid fields
  foreach ($data as $key => $value) {
   if (in_array($key, [
    'name', 'species', 'breed',
    'color', 'description', 'photo_url', 'status'
   ])) {
    $fields[] = "$key = :$key";
    $params[":$key"] = $value;
   }
  }

  // If owner has no pets, nothing to update
  if (empty($fields)) return false;

  // Join all field assignments for SQL update
  $sql = "UPDATE pets
          SET " . implode(', ', $fields) . "
          WHERE id = :id AND user_id = :user_id";
  $stmt = $this->_dbHandle->prepare($sql);
  $stmt->execute($params);

  // True if rows affected
  return $stmt->rowCount() > 0;
 }

 // Delete a pet (owner only)
 public function deletePet($petId, $user_id): bool {
  // Check pet id and user id to ensure ownership
  $sql = "DELETE FROM pets
          WHERE id = :id AND user_id = :user_id";
  $stmt = $this->_dbHandle->prepare($sql);
  // Bind securely
  $stmt->bindValue(':id', $petId, PDO::PARAM_INT);
  $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
  $stmt->execute();

  // True if delete succeeded
  return $stmt->rowCount() > 0;
 }
}
