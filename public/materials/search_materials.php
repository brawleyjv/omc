<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php'; // Corrected path to config.php
require_once BASE_PATH . '/Models/Database.php';
require_once BASE_PATH . '/Models/Material.php';
require_once BASE_PATH . '/Controllers/MaterialController.php';

use MyApp\Controllers\MaterialController;
use MyApp\Models\Database;
use Globals\Config;

// Instantiate the Database class with arguments
$database = new Database(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME); // Updated initialization
$controller = new MaterialController($database); // This will now work as expected

$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';

if ($searchTerm) {
    $materials = $controller->searchMaterials($searchTerm);
} else {
    $materials = [];
}

include BASE_PATH . '/Views/materials/search_materials.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- ...existing code... -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>styles.css"> <!-- Updated to use BASE_URL -->
</head>
<body>
    <!-- ...existing code... -->
</body>
</html>

