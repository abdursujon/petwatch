<?php

/**
 * Class Database designed to establish connection
 * to the database petwatch.sqlite
 */
class Database {
 /**
  * @var Database
  */
 protected static $_dbInstance = null;

 /**
  * @var PDO
  */
 protected $_dbHandle;

 /**
  * @return Database
  */
 public static function getInstance() {
  // Checks if the PDO exists
  if (self::$_dbInstance === null) {
   // Creates new instance if not, sending in connection info
   self::$_dbInstance = new self();
  }
  return self::$_dbInstance;
 }

 /**
  * Private constructor to initialize the database connection.
  * Uses SQLite and sets error handling to exception mode.
  * Terminates the script if the connection fails.
  */
 private function __construct() {
  try {
   $this->_dbHandle = new PDO("sqlite:petwatch.sqlite");
   // Handles error
   $this->_dbHandle->setAttribute(
    PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION
   );
  } catch (PDOException $e) {
   echo "Database Connection Error: " . $e->getMessage();
   // Stop script execution on connection failure
   die();
  }
 }

 /**
  * @return PDO
  */
 public function getdbConnection() {
  // Returns the PDO handle to be used elsewhere
  return $this->_dbHandle;
 }

 /**
  * Destroys the PDO handle when no longer needed
  */
 public function __destruct() {
  $this->_dbHandle = null;
 }
}
