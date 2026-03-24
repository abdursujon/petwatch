<?php
require_once('Database.php');
require_once('UserData.php');

class UserDataSet
{
  protected $_dbHandle, $_dbInstance;

  public function __construct()
  {
    $this->_dbInstance = Database::getInstance();
    $this->_dbHandle =
      $this->_dbInstance->getdbConnection();
  }

  public function getUserByUsername($username)
  {
    $sqlQuery = "SELECT * FROM users
                     WHERE username = :username
                     LIMIT 1";
    $statement = $this->_dbHandle->prepare($sqlQuery);
    $statement->bindParam(':username', $username);
    $statement->execute();

    $row = $statement->fetch();
    if ($row) {
      return new UserData($row);
    }
    return null;
  }
}
