<?php
require_once('../Models/Database.php');

$db = Database::getInstance();
$dbHandle = $db->getdbConnection();

$rows = $dbHandle->query("SELECT id FROM locations ORDER BY id ASC LIMIT 2000")->fetchAll();

$stmt = $dbHandle->prepare("UPDATE locations SET pet_id = ? WHERE id = ?");

for ($i = 999; $i < count($rows); $i++) {
    $petId = rand(1, 1000);
    $stmt->execute([$petId, $rows[$i]['id']]);
}

echo "Done! Updated pet_id 1-500 for first 500 location rows.\n";