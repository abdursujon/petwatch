<?php
require_once('../Models/Database.php');

$db = Database::getInstance();
$dbHandle = $db->getdbConnection();

$names = ['Buddy', 'Max', 'Charlie', 'Bella', 'Luna', 'Daisy', 'Rocky', 'Milo', 'Coco', 'Oscar',
    'Leo', 'Lola', 'Teddy', 'Ruby', 'Rosie', 'Archie', 'Poppy', 'Alfie', 'Molly', 'Ziggy',
    'Zeus', 'Nala', 'Simba', 'Pepper', 'Ginger', 'Shadow', 'Storm', 'Biscuit', 'Oreo', 'Patch',
    'Jasper', 'Willow', 'Felix', 'Cleo', 'Honey', 'Bruno', 'Misty', 'Chester', 'Toffee', 'Socks',
    'Frankie', 'Pickle', 'Bandit', 'Maple', 'Olive', 'Scout', 'Ivy', 'Rusty', 'Hazel', 'Finn'];

$stmt = $dbHandle->prepare("UPDATE pets SET name = ? WHERE id = ?");

for ($id = 400; $id <= 500; $id++) {
    $name = $names[array_rand($names)];
    $stmt->execute([$name, $id]);
}

echo "Done! Updated pets 400-500.\n";
