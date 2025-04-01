<?php
require_once realpath(dirname(__FILE__) . '/../../../config.php'); // Updated to use realpath
require_once BASE_PATH . '/Models/Database.php'; // Ensure Database class is included

use MyApp\Models\Database; // Import Database class

$database = new Database(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME); // Updated initialization
$connection = $database->getConnection(); // Assuming getConnection() returns a PDO instance

if (!$connection) {
    die("Connection failed.");
}

$sql = "SELECT materials.*, vendors.Vendor AS vendor_name FROM materials 
        LEFT JOIN vendors ON materials.vendor = vendors.Vendor_ID";
$stmt = $connection->prepare($sql); // Use PDO's prepare method
$stmt->execute(); // Execute the query
$result = $stmt->fetchAll(PDO::FETCH_ASSOC); // Fetch all results as an associative array
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>List of Materials</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>styles.css"> <!-- Updated to use BASE_URL -->
    <style>
        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid #ccc;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        .action-buttons {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .button {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        .print-button {
            background-color: #007BFF;
            color: white;
        }
        .print-button:hover {
            background-color: #0056b3;
        }
        .close-button {
            background-color: #DC3545;
            color: white;
        }
        .close-button:hover {
            background-color: #c82333;
        }
        .open-button {
            background-color: #28a745;
            color: white;
            text-decoration: none;
            padding: 5px 10px;
            border-radius: 5px;
        }
        .open-button:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="action-buttons">
            <button class="print-button" onclick="window.print()">Print</button>
            <button class="close-button" onclick="window.location.href='<?php echo BASE_URL; ?>public/materials/index.php'">Close</button>
        </div>
        <h1>List of Materials</h1>
        <table>
            <thead>
                <tr>
                    <th>Item Number</th>
                    <th>Description</th>
                    <th>Length (in inches)</th>
                    <th>Width (in inches)</th>
                    <th>Thickness (in inches)</th>
                    <th>Price</th>
                    <th>Quantity on Hand</th>
                    <th>Vendor</th>
                    <th>Type</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (!empty($result)) {
                    foreach ($result as $row) {
                        echo "<tr>
                                <td>" . htmlspecialchars($row['Item_no'] ?? '') . "</td>
                                <td>" . htmlspecialchars($row['Description'] ?? '') . "</td>
                                <td>" . htmlspecialchars($row['Length'] ?? '') . "</td>
                                <td>" . htmlspecialchars($row['Width'] ?? '') . "</td>
                                <td>" . htmlspecialchars($row['Thickness'] ?? '') . "</td>
                                <td>" . htmlspecialchars($row['Price'] ?? '') . "</td>
                                <td>" . htmlspecialchars($row['Quantity_on_Hand'] ?? '') . "</td>
                                <td>" . htmlspecialchars($row['vendor_name'] ?? '') . "</td>
                                <td>" . htmlspecialchars($row['type'] ?? '') . "</td>
                                <td><a href='" . BASE_URL . "public/materials/material.php?material_id=" . htmlspecialchars($row['id'] ?? '') . "' class='open-button'>Open</a></td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='10'>No materials found</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>
<?php
// No need to close the PDO connection explicitly; it will close automatically when the script ends.
?>
