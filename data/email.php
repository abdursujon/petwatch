<?php
require_once('../Models/Database.php');

$db = Database::getInstance();
$dbHandle = $db->getdbConnection();

$domains = ['gmail.com', 'hotmail.com', 'outlook.com'];
$passwordHash = password_hash('password123', PASSWORD_DEFAULT);

$stmt = $dbHandle->prepare("INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, 'Owner')");

for ($i = 500; $i <= 1000; $i++) {
    $domain = $domains[array_rand($domains)];
    $stmt->execute(['user' . $i, 'user' . $i . '@' . $domain, $passwordHash]);
}

echo "Done! Inserted user 500-1000.\n";