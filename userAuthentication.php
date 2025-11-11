<?php
// Inheritance the UserAuthentication model class to
// implement authentication
require_once('Models/UserAuthentication.php');

/**
 * We will use UserAuthentication class function
 * hashUserPassword to hash passwords in the database.
 *
 * Note: For ease and testing purposes we will
 *   1. Use same password for all Owners:
 *      O7m!eX#2vLp9@zFq
 *   2. Use same password for all Users:
 *      U$4PzN!w8Hq@1rBx
 */
$userAuthentication = new UserAuthentication();
$rows = $userAuthentication->hasUserPassword(
  'O7m!eX#2vLp9@zFq', 'U$4PzN!w8Hq@1rBx'
);

/**
 * Test if the hashing process is successful and print a
 * message (for developer testing) showing how many rows
 * have been hashed correctly.
 *
 * In this project, the expected number of rows is 210.
 */
echo "Updated password hashes for $rows users.";
