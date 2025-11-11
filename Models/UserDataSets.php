<?php
// Inheritance Database and UserData class models
require_once('Database.php');
require_once('UserData.php');

/**
 * Class UserDataSet
 * Handles interactions with the users table in the
 * database. Responsible for fetching user data
 * and returning it as UserData objects.
 */
class UserDataSet {
 /**
  * @var PDO $_dbHandle PDO database connection handle.
  * @var Database $_dbInstance Singleton instance
  * of the Database class.
  */
 protected $_dbHandle, $_dbInstance;

 /**
  * UserDataSet constructor.
  * Initializes database connection using the
  * Database singleton.
  */
 public function __construct() {
  $this->_dbInstance = Database::getInstance();
  $this->_dbHandle =
   $this->_dbInstance->getdbConnection();
 }

 /**
  * Retrieve a single user record by username.
  * Used to check if a username already exists
  * (e.g., during registration).
  */
 public function getUserByUsername($username) {
  // Check username existence, limit to one
  $sqlQuery = "SELECT * FROM users
               WHERE username = :username
               LIMIT 1";
  $statement = $this->_dbHandle->prepare($sqlQuery);
  $statement->bindParam(':username', $username);
  $statement->execute();

  // Fetch row and return as UserData object
  $row = $statement->fetch();
  if ($row) {
   return new UserData($row);
  }
  return null;
 }
}
