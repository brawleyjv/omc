<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/OMC/config.php'; // Ensure correct path to config.php
require_once BASE_PATH . '/Models/Database.php';
require_once BASE_PATH . '/Controllers/VendorController.php'; // Corrected path

use MyApp\Controllers\VendorController; // Use the correct namespace
use MyApp\Models\Database; // Add namespace for Database

// Ensure DB constants are defined
if (!defined('DB_HOST') || !defined('DB_NAME') || !defined('DB_USER') || !defined('DB_PASSWORD')) {
    die('Database configuration constants are not defined. Please check config.php.');
}

$database = new Database(DB_HOST, DB_USER, '', DB_NAME); // Removed DB_PASSWORD
$vendorController = new VendorController($database); // Pass Database instance to VendorController

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_vendor_id'])) {
    $vendorId = $_POST['delete_vendor_id'];
    $vendorController->deleteVendor($vendorId);
    header('Location: list_vendors.php');
    exit;
}

$vendors = $vendorController->getVendors();
?>

<?php require_once BASE_PATH . '/Views/header.php'; ?> <!-- Include header -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>List of Vendors</title>
    <link rel="stylesheet" type="text/css" href="<?php echo BASE_URL; ?>styles.css"> <!-- Ensure BASE_URL is used correctly -->
    <style>
        .top-buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 0px;
        }
        .center-title {
            text-align: center;
            margin-top: 20px;
        }
        .content {
            margin-top: 0px;
        }
        .button {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            background-color: #007BFF;
            color: white;
            text-decoration: none;
            font-size: 16px;
            cursor: pointer;
            display: inline-block;
            margin-top: 20px;
        }
        .button:hover {
            background-color: #0056b3;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 2px solid #007BFF;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        .btn.styled-btn.red {
            background-color: #DC3545;
            color: white;
            padding: 5px 10px;
            font-size: 14px;
            border: none;
        }
        .btn.styled-btn.red:hover {
            background-color: #c82333;
        }
        .btn.styled-btn {
            padding: 5px 10px;
            font-size: 14px;
            border: none;
        }
        .btn.styled-btn.white {
            background-color: white;
            color: #007BFF;
            padding: 5px 10px;
            font-size: 14px;
            border: 2px solid #007BFF;
        }
        .btn.styled-btn.white:hover {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <h1 class="center-title">List of Vendors</h1>
    <div class="top-buttons">
        <button class="btn styled-btn" style="margin-right: 20px;" onclick="window.location.href='<?php echo BASE_URL; ?>Views/vendors/add_vendor.php'">Add Vendor</button>
        <button class="btn styled-btn red" onclick="window.location.href='<?php echo BASE_URL; ?>Views/vendors/index.php'">Close</button> <!-- Close button -->
    </div>
    <div class="content">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Vendor</th>
                    <th>Phone</th>
                    <th>Web Address</th>
                    <th>Mailing Address</th>
                    <th>Email Address</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($vendors)): ?>
                    <?php foreach ($vendors as $vendor): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($vendor['id']); ?></td>
                            <td><?php echo htmlspecialchars($vendor['Vendor']); ?></td>
                            <td><?php echo htmlspecialchars($vendor['phone']); ?></td>
                            <td><?php echo htmlspecialchars($vendor['web_address']); ?></td>
                            <td><?php echo htmlspecialchars($vendor['mailing_address']); ?></td>
                            <td><?php echo htmlspecialchars($vendor['email_address']); ?></td>
                            <td>
                                <a href="edit_vendor.php?vendor_id=<?php echo htmlspecialchars($vendor['id']); ?>" class="btn styled-btn white">Edit</a>
                                <form action="list_vendors.php" method="post" onsubmit="return confirm('Are you sure you want to delete this vendor?');" style="display:inline;">
                                    <input type="hidden" name="delete_vendor_id" value="<?php echo htmlspecialchars($vendor['id']); ?>">
                                    <input type="submit" class="btn styled-btn red" value="Delete">
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7">No vendors found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>