<?php

/**
 * Singleton database connection manager.
 * Establishes and provides a shared PDO connection to the petwatch SQLite database.
 */
class Database {
    protected static $_dbInstance = null;
    protected $_dbHandle;

    public static function getInstance() {
        if (self::$_dbInstance === null) {
            self::$_dbInstance = new self();
        }
        return self::$_dbInstance;
    }

    private function __construct() {
        try {
            $this->_dbHandle = new PDO("sqlite:petwatch.sqlite");
            $this->_dbHandle->setAttribute(
                PDO::ATTR_ERRMODE,
                PDO::ERRMODE_EXCEPTION
            );
        } catch (PDOException $e) {
            echo "Database Connection Error: " . $e->getMessage();
            die();
        }
    }

    public function getdbConnection() {
        return $this->_dbHandle;
    }

    public function __destruct() {
        $this->_dbHandle = null;
    }
}
