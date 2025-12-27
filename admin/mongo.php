<?php
require_once __DIR__ . '/../vendor/autoload.php';

$manager = new MongoDB\Driver\Manager("mongodb://localhost:27017");
$dbName = "cs306";
$collection = "tickets";
?>
