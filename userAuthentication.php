<?php
require_once('models/UserAuthentication.php');

/**
 * This controller helps us hash all user and owner password to the database.
 * When run from the browser, or terminal, this script updates all hashing for all type of user.
 */
$userAuthentication = new UserAuthentication();
$rows = $userAuthentication->hasUserPassword(
  'O7m!eX#2vLp9@zFq',
  'U$4PzN!w8Hq@1rBx'
);

echo "Updated password hashes for $rows users.";
