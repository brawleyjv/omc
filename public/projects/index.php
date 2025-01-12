<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use Controllers\InstallController;
use Models\Settings;
use MyApp\Models\Database;
use Globals\Config; // Ensure Config is imported

require_once '../../Globals/Config.php';
require_once '../../Controllers/InstallController.php';
require_once '../../Models/Settings.php';
require_once '../../Models/Database.php';

// Ensure session is started only if not already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$settings = new Settings();
$settings_data = $settings->getSettings();
$_SESSION['company_name'] = $settings_data['company_name'];
$_SESSION['company_slogan'] = $settings_data['company_slogan'];

$db = new Database(Config::DB_HOST, Config::DB_NAME, Config::DB_USER, Config::DB_PASS);
$conn = $db->getConnection();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST['name'] ?? '');

    if (empty($name)) {
        header("Location: index.php?error=Name is required");
        exit();
    }

    $stmt = $conn->prepare("SELECT * FROM users WHERE name = :name");
    if (!$stmt) {
        error_log("Prepared statement failed: " . $conn->errorInfo()[2]);
        echo "Prepared statement failed: " . $conn->errorInfo()[2]; // Debug output
        header("Location: index.php?error=An unexpected error occurred");
        exit();
    }

    $stmt->bindParam(':name', $name);

    if ($stmt->execute()) {
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($result) > 0) {
            $_SESSION['username'] = $name;
            header("Location: main.php");
            exit();
        } else {
            header("Location: Users/register.php?name=" . urlencode($name));
            exit();
        }
    } else {
        error_log("Database error: " . $stmt->errorInfo()[2]);
        echo "Database error: " . $stmt->errorInfo()[2]; // Debug output
        header("Location: index.php?error=An unexpected error occurred");
        exit();
    }
}
?>


