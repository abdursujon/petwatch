<?php
require_once('Database.php');
require_once('PetData.php');

class PetDataSets
{
  public $_dbHandle;
  protected $_dbInstance;

  public function __construct()
  {
    $this->_dbInstance = Database::getInstance();
    $this->_dbHandle = $this->_dbInstance->getdbConnection();
  }

  public function getPetByUserID($user_id): array
  {
    $sql = "SELECT * FROM pets WHERE user_id = :user_id ORDER BY id DESC";
    $stmt = $this->_dbHandle->prepare($sql);
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();

    $pets = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
      $pets[] = new PetData($row);
    }
    return $pets;
  }

  public function addPet(
    $user_id, $name, $species, $breed,
    $color, $description, $photo_url = null
  )
  {
    $sql = "INSERT INTO pets
                (user_id, name, species, breed, color,
                 description, photo_url, status)
                VALUES
                (:user_id, :name, :species, :breed, :color,
                 :description, :photo_url, 'lost')";

    $stmt = $this->_dbHandle->prepare($sql);
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':name', $name);
    $stmt->bindValue(':species', $species);
    $stmt->bindValue(':breed', $breed);
    $stmt->bindValue(':color', $color);
    $stmt->bindValue(':description', $description);
    $stmt->bindValue(':photo_url', $photo_url);
    $stmt->execute();

    return $this->_dbHandle->lastInsertId();
  }

  public function updatePet($petId, $user_id, $data): bool
  {
    $fields = [];
    $params = [':id' => $petId, ':user_id' => $user_id];

    foreach ($data as $key => $value) {
      if (in_array($key, [
        'name', 'species', 'breed',
        'color', 'description', 'photo_url', 'status'
      ], true)) {
        $fields[] = "$key = :$key";
        $params[":$key"] = $value;
      }
    }

    if (empty($fields)) {
      return false;
    }

    $sql = "UPDATE pets
                SET " . implode(', ', $fields) . "
                WHERE id = :id AND user_id = :user_id";
    $stmt = $this->_dbHandle->prepare($sql);
    $stmt->execute($params);

    return $stmt->rowCount() > 0;
  }

  public function deletePet($petId, $user_id): bool
  {
    $sql = "DELETE FROM pets
                WHERE id = :id AND user_id = :user_id";
    $stmt = $this->_dbHandle->prepare($sql);
    $stmt->bindValue(':id', $petId, PDO::PARAM_INT);
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->rowCount() > 0;
  }
}
