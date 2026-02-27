<?php
require_once('../Models/Database.php');

$db = Database::getInstance();
$dbHandle = $db->getdbConnection();
$stmt = $dbHandle->prepare("UPDATE users SET username = ? WHERE id = ?");

for ($i = 501; $i <= 1000; $i++) {
    $stmt->execute(['owner' . $i, $i + 2]);
}

echo "Done! Updated 1000 usernames.\n";

//
//UPDATE users SET role = 'User' Where role = 'Owner' AND id>500;