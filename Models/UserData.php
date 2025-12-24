<?php

/**
 * Data model representing a single user record.
 * Encapsulates user attributes loaded from the database and
 * provides read-only access to authentication and identity data.
 */
class UserData {
    protected $_id, $_username, $_email,
              $_password_hash, $_role;

    public function __construct($dbRow) {
        $this->_id = $dbRow['id'];
        $this->_username = $dbRow['username'];
        $this->_email = $dbRow['email'];
        $this->_password_hash = $dbRow['password_hash'];
        $this->_role = $dbRow['role'];
    }

    public function getUserId() {
        return $this->_id;
    }

    public function getUsername() {
        return $this->_username;
    }

    public function getEmail() {
        return $this->_email;
    }

    public function getPasswordHash() {
        return $this->_password_hash;
    }

    public function getRole() {
        return $this->_role;
    }
}
