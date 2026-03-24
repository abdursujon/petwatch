<?php
/**
 * Connect Poseidon MariaDB database.
 * Secure connection to the petwatch database hosted on University Server.
 */
class Database
{
  protected static $_dbInstance = null;
  protected $_dbHandle;

  // Ensures that only one database connection exists througout the app.
  public static function getInstance()
  {
    if (self::$_dbInstance === null) {
      self::$_dbInstance = new self();
    }
    return self::$_dbInstance;
  }


  // Database connection with poseidon MariaDB.
  private function __construct()
  {
    try {
      $dbName = "sge366";
      $username = "sge366";
      $password = getenv('db_password');
      $host = '127.0.0.1';

      if (!$password) {
        throw new Exception("DB password env var not set");
      }

      $this->_dbHandle = new PDO(
        "mysql:host=$host;port=3306;dbname=$dbName",
        $username,
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
      );
    } catch (PDOException $e) {
      die("Database Connection Error: " . $e->getMessage());
    }
  }

  public function getdbConnection()
  {
    return $this->_dbHandle;
  }

  public function __destruct()
  {
    $this->_dbHandle = null;
  }
}
