<?php
// Inheritance the database connection using this two models
require_once('Database.php');
require_once('UserData.php');

// This class is used to authenticate user
// and owner with password hash functionality
class UserAuthentication {
 private $_dbHandle;

 // Get the database connection by inheritance
 public function __construct() {
  $this->_dbHandle =
   Database::getInstance()->getdbConnection();
 }

 /**
  * Authenticate a user by username and password.
  * Returns a UserData object if successful, null otherwise.
  */
 public function verifyLogin($username, $password): ?UserData {
  // Select user record by username (limit to one)
  $sql = "SELECT * FROM users
          WHERE username = :username
          LIMIT 1";
  $stmt = $this->_dbHandle->prepare($sql);
  $stmt->bindValue(':username', $username);
  $stmt->execute();

  // Fetch the user data as an associative array
  $row = $stmt->fetch(PDO::FETCH_ASSOC);

  // Verify the password against hashed password in db
  if ($row && password_verify($password,
      $row['password_hash'])) {
   // Return the matched user details
   return new UserData($row);
  }

  // If credentials incorrect, return null
  return null;
 }

 /**
  * Updates hashed password in the database.
  * Each time called, rehashes all user passwords.
  * Implemented by userAuthentication.php controller.
  */
 public function hasUserPassword(
  $ownerPassword, $userPassword
 ): int {
  // Select all user ids and roles
  $sql = "SELECT id, role FROM users";
  $stmt = $this->_dbHandle->prepare($sql);
  $stmt->execute();
  $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

  // Prepare update query for hashed password
  $update = $this->_dbHandle->prepare("
   UPDATE users SET password_hash = :hash WHERE id = :id
  ");

  $count = 0;

  // Hash password for each user
  foreach ($users as $user) {
   // Hash for owner or normal user accordingly
   $hash = password_hash(
    $user['role'] === 'Owner'
     ? $ownerPassword : $userPassword,
    PASSWORD_DEFAULT
   );

   // Update hashed password
   $update->execute([
    ':hash' => $hash,
    ':id' => $user['id']
   ]);

   // Increment total updated count
   $count += $update->rowCount();
  }

  // Return how many users were updated
  return $count;
 }
}
