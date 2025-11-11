<?php

/**
 * Class UserData is designed to initialise get functions
 * to retrieve user data.
 *
 * It works as a data model for UserDataSet class.
 */
class UserData {
 /**
  * @var int $_id User's unique identifier.
  * @var string $_username User's username.
  * @var string $_email User's email address.
  * @var string $_password_hash User's hashed password.
  * @var string $_role User's role (Owner/User).
  */
 protected $_id, $_username, $_email,
           $_password_hash, $_role;

 // Constructor of class UserData
 public function __construct($dbRow) {
  $this->_id = $dbRow['id'];
  $this->_username = $dbRow['username'];
  $this->_email = $dbRow['email'];
  $this->_password_hash = $dbRow['password_hash'];
  $this->_role = $dbRow['role'];
 }

 // Get user id
 public function getUserId() {
  return $this->_id;
 }

 // Get username
 public function getUsername() {
  return $this->_username;
 }

 // Get user email address
 public function getEmail() {
  return $this->_email;
 }

 // Get password hashed in the database
 public function getPasswordHash() {
  return $this->_password_hash;
 }

 // Get user role (Owner/User)
 public function getRole() {
  return $this->_role;
 }
}
