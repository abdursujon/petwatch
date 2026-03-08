<?php
require_once('Models/UserAuthentication.php');

/**
 * One-time password hashing utility.
 * Rehashes all user passwords in the database using fixed
 * credentials for Owners and Users (testing/setup only).
 */
$userAuthentication = new UserAuthentication();
$rows = $userAuthentication->hasUserPassword(
  'O7m!eX#2vLp9@zFq',
  'U$4PzN!w8Hq@1rBx'
);

echo "Updated password hashes for $rows users.";
