<?php
require_once __DIR__ . '/../../Globals/Config.php';
require_once __DIR__ . '/../../Models/Database.php';
require_once __DIR__ . '/../../Controllers/EstimateController.php';

use MyApp\Models\Database;
use MyApp\Controllers\EstimateController;
use Globals\Config;

// Establish database connection
$database = new Database(Config::DB_HOST, Config::DB_NAME, Config::DB_USER, Config::DB_PASS);
$conn = $database->getConnection();
$estimateController = new EstimateController($database);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle form submission to add estimate
    // Add your code here to process the form data and add the estimate
}
?>