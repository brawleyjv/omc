<?php
include '../header.php';
include '../config.php';

$connection = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (!$connection) {
    die("Connection failed: " . mysqli_connect_error());
}

$sql = "SELECT materials.*, vendors.Vendor AS vendor_name FROM materials 
        LEFT JOIN vendors ON materials.vendor = vendors.Vendor_ID";
$result = mysqli_query($connection, $sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>List of Materials</title>
    <link rel="stylesheet" href="..//public/css/">
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
            <button class="close-button" onclick="window.location.href='index.php'">Close</button>
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
                if (mysqli_num_rows($result) > 0) {
                    while($row = mysqli_fetch_assoc($result)) {
                        echo "<tr>
                                <td>" . (isset($row['Item_no']) ? $row['Item_no'] : '') . "</td>
                                <td>" . (isset($row['Description']) ? $row['Description'] : '') . "</td>
                                <td>" . (isset($row['Length']) ? $row['Length'] : '') . "</td>
                                <td>" . (isset($row['Width']) ? $row['Width'] : '') . "</td>
                                <td>" . (isset($row['Thickness']) ? $row['Thickness'] : '') . "</td>
                                <td>" . (isset($row['Price']) ? $row['Price'] : '') . "</td>
                                <td>" . (isset($row['Quantity_on_Hand']) ? $row['Quantity_on_Hand'] : '') . "</td>
                                <td>" . (isset($row['vendor_name']) ? $row['vendor_name'] : '') . "</td>
                                <td>" . (isset($row['type']) ? $row['type'] : '') . "</td>
                                <td><a href='material.php?material_id=" . $row['id'] . "' class='open-button'>Open</a></td>
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
mysqli_close($connection);
?>
