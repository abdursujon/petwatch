// Test database connection
<?php
require_once __DIR__ . '/../Models/Database.php';

try {
    $db = Database::getInstance();
    $connection = $db->getdbConnection();

    if($connection){
        echo "Database Connection Success: ";
        echo "Connection type: " . $connection-> getAttribute(PDO::ATTR_DRIVER_NAME) . "\n";
    }
} catch (Exception $e){
    echo "Database Connection Error: " . $e->getMessage();
}
