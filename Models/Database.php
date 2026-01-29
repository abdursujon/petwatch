<?php

/**
 * Singleton database connection manager.
 * Establishes and provides a shared PDO connection to the petwatch SQLite database.
 */
class Database
{
    protected static $_dbInstance = null;
    protected $_dbHandle;

    public static function getInstance()
    {
        if (self::$_dbInstance === null) {
            self::$_dbInstance = new self();
        }
        return self::$_dbInstance;
    }

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
