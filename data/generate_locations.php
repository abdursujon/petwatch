<?php
require_once('../Models/Database.php');

$db = Database::getInstance();
$dbHandle = $db->getdbConnection();

// Clear old location data
$dbHandle->exec("DELETE FROM locations");
$dbHandle->exec("UPDATE sightings SET latitude = NULL, longitude = NULL, timestamp = NULL");

// Every pet 1-500 gets at least one location
for ($pet_id = 1; $pet_id <= 500; $pet_id++) {
    $lat = 53.44 + (mt_rand(0, 80000) / 1000000);
    $lng = -2.31 + (mt_rand(0, 130000) / 1000000);
    $timestamp = date('Y-m-d H:i:s', strtotime('-' . rand(0, 90) . ' days -' . rand(0, 23) . ' hours -' . rand(0, 59) . ' minutes'));

    $stmt = $dbHandle->prepare("INSERT INTO locations (pet_id, latitude, longitude, timestamp) VALUES (?, ?, ?, ?)");
    $stmt->execute([$pet_id, $lat, $lng, $timestamp]);

    $stmt = $dbHandle->prepare("UPDATE sightings SET latitude = ?, longitude = ?, timestamp = ? WHERE pet_id = ?");
    $stmt->execute([$lat, $lng, $timestamp, $pet_id]);
}

// 1500 extra random ones for locations
for ($i = 0; $i < 1500; $i++) {
    $pet_id = rand(1, 500);
    $lat = 53.44 + (mt_rand(0, 80000) / 1000000);
    $lng = -2.31 + (mt_rand(0, 130000) / 1000000);
    $timestamp = date('Y-m-d H:i:s', strtotime('-' . rand(0, 90) . ' days -' . rand(0, 23) . ' hours -' . rand(0, 59) . ' minutes'));

    $stmt = $dbHandle->prepare("INSERT INTO locations (pet_id, latitude, longitude, timestamp) VALUES (?, ?, ?, ?)");
    $stmt->execute([$pet_id, $lat, $lng, $timestamp]);
}

echo "Done! Inserted 2000 locations and updated sightings.\n";