<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php'; // Use $_SERVER['DOCUMENT_ROOT'] for config.php
require_once BASE_PATH . 'Models/Database.php'; // Use BASE_PATH for dynamic path resolution

use MyApp\Models\Database;

// Start the session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in
//if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if not logged in
   // header("Location: " . BASE_URL . "Views/settings.php");
    //exit();
//}

// Establish database connection
$database = new Database(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);$conn = $database->getConnection();

// Fetch existing setup values
$query = "SELECT * FROM setup LIMIT 1";
$stmt = $conn->prepare($query);
$stmt->execute();
$setup = $stmt->fetch(PDO::FETCH_ASSOC);

// Initialize variables with existing values or empty strings
$mill_rate = $setup['mill_rate'] ?? '';
$laser_rate = $setup['laser_rate'] ?? '';
$bit_change_rate = $setup['bit_change_rate'] ?? '';
$customize_rate = $setup['customize_rate'] ?? '';
$overhead_rate = $setup['overhead_rate'] ?? '';
$labor_rate = $setup['labor_rate'] ?? '';
$sqf_milling_rate = $setup['sqf_milling_rate'] ?? '';
$packaging_rate = $setup['packaging_rate'] ?? '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mill_rate = $_POST['mill_rate'] ?? null;
    $laser_rate = $_POST['laser_rate'] ?? null;
    $bit_change_rate = $_POST['bit_change_rate'] ?? null;
    $customize_rate = $_POST['customize_rate'] ?? null;
    $overhead_rate = $_POST['overhead_rate'] ?? null;
    $labor_rate = $_POST['labor_rate'] ?? null;
    $sqf_milling_rate = $_POST['sqf_milling_rate'] ?? null;
    $packaging_rate = $_POST['packaging_rate'] ?? null;

    if ($setup) {
        // Update existing setup values
        $query = "UPDATE setup SET mill_rate = :mill_rate, laser_rate = :laser_rate, bit_change_rate = :bit_change_rate, customize_rate = :customize_rate, overhead_rate = :overhead_rate, labor_rate = :labor_rate, sqf_milling_rate = :sqf_milling_rate, packaging_rate = :packaging_rate WHERE id = :id";
        $stmt = $conn->prepare($query);
        $stmt->bindValue(':mill_rate', $mill_rate);
        $stmt->bindValue(':laser_rate', $laser_rate);
        $stmt->bindValue(':bit_change_rate', $bit_change_rate);
        $stmt->bindValue(':customize_rate', $customize_rate);
        $stmt->bindValue(':overhead_rate', $overhead_rate);
        $stmt->bindValue(':labor_rate', $labor_rate);
        $stmt->bindValue(':sqf_milling_rate', $sqf_milling_rate);
        $stmt->bindValue(':packaging_rate', $packaging_rate);
        $stmt->bindValue(':id', $setup['id'], PDO::PARAM_INT);
        $stmt->execute();
    } else {
        // Insert new setup values
        $query = "INSERT INTO setup (mill_rate, laser_rate, bit_change_rate, customize_rate, overhead_rate, labor_rate, sqf_milling_rate, packaging_rate) VALUES (:mill_rate, :laser_rate, :bit_change_rate, :customize_rate, :overhead_rate, :labor_rate, :sqf_milling_rate, :packaging_rate)";
        $stmt = $conn->prepare($query);
        $stmt->bindValue(':mill_rate', $mill_rate);
        $stmt->bindValue(':laser_rate', $laser_rate);
        $stmt->bindValue(':bit_change_rate', $bit_change_rate);
        $stmt->bindValue(':customize_rate', $customize_rate);
        $stmt->bindValue(':overhead_rate', $overhead_rate);
        $stmt->bindValue(':labor_rate', $labor_rate);
        $stmt->bindValue(':sqf_milling_rate', $sqf_milling_rate);
        $stmt->bindValue(':packaging_rate', $packaging_rate);
        $stmt->execute();
    }

    // Redirect to main.php after updating
    header("Location: ../Views/main.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>styles.css"> <!-- Corrected path -->
</head>
<body>
    <?php include BASE_PATH . 'Views/header.php'; ?> <!-- Use BASE_PATH -->
    <div class="container">
        <h1 class="title">Setup</h1>
        <form action="setup.php" method="post">
            <div class="form-group">
                <label for="mill_rate">Mill Rate:</label>
                <input type="text" id="mill_rate" name="mill_rate" value="<?php echo htmlspecialchars($mill_rate); ?>">
            </div>
            <div class="form-group">
                <label for="laser_rate">Laser Rate:</label>
                <input type="text" id="laser_rate" name="laser_rate" value="<?php echo htmlspecialchars($laser_rate); ?>">
            </div>
            <div class="form-group">
                <label for="bit_change_rate">Bit Change Rate:</label>
                <input type="text" id="bit_change_rate" name="bit_change_rate" value="<?php echo htmlspecialchars($bit_change_rate); ?>">
            </div>
            <div class="form-group">
                <label for="customize_rate">Customize Rate:</label>
                <input type="text" id="customize_rate" name="customize_rate" value="<?php echo htmlspecialchars($customize_rate); ?>">
            </div>
            <div class="form-group">
                <label for="overhead_rate">Overhead Rate:</label>
                <input type="text" id="overhead_rate" name="overhead_rate" value="<?php echo htmlspecialchars($overhead_rate); ?>">
            </div>
            <div class="form-group">
                <label for="labor_rate">Labor Rate:</label>
                <input type="text" id="labor_rate" name="labor_rate" value="<?php echo htmlspecialchars($labor_rate); ?>">
            </div>
            <div class="form-group">
                <label for="sqf_milling_rate">SQF Milling Rate:</label>
                <input type="text" id="sqf_milling_rate" name="sqf_milling_rate" value="<?php echo htmlspecialchars($sqf_milling_rate); ?>">
            </div>
            <div class="form-group">
                <label for="packaging_rate">Packaging Rate:</label>
                <input type="text" id="packaging_rate" name="packaging_rate" value="<?php echo htmlspecialchars($packaging_rate); ?>">
            </div>
            <div class="button-container">
                <button type="submit" class="btn styled-btn">Save Changes</button>
                <button type="button" class="btn styled-btn red" onclick="window.location.href='<?php echo BASE_URL; ?>index.php'">Close</button>
            </div>
        </form>
    </div>
</body>
</html>
