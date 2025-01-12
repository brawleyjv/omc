<?php
// filepath: /c:/xampp/htdocs/OMC/public/projects/search_projects.php

require_once __DIR__ . '/../../Globals/Config.php';
require_once __DIR__ . '/../../Models/Database.php';
require_once __DIR__ . '/../../Models/material.php';
require_once __DIR__ . '/../../Controllers/MaterialController.php';
use MyApp\Models\Database;
use MyApp\Controllers\MaterialController;

// Ensure Database is instantiated with required arguments
$database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS);
$projectsController = new MaterialController($database);

$search_term = isset($_GET['search_term']) ? $_GET['search_term'] : '';

$results = [];
$noResults = false;
if (!empty($search_term)) {
    // Ensure searchProjects method exists in ProjectController
    $results = $projectsController->searchMaterial($search_term);
    if (empty($results)) {
        $noResults = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Materials</title>
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        .container {
            margin-top: -50px; /* Reduced top margin */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Added shadow box */
            padding: 20px;
            background-color: #fff;
        }
        .results-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px; /* Reduced top margin */
            table-layout: fixed; /* Ensure the table fits within the container */
        }
        .results-table th, .results-table td {
            border: 1px solid #007BFF;
            padding: 8px;
            text-align: left;
            word-wrap: break-word; /* Ensure long words break to fit within the cell */
        }
        .results-table th {
            background-color: #007BFF;
            color: white;
        }
        .results-table tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .results-table tr:hover {
            background-color: #ddd;
        }
        .results-container {
            margin-top: 10px; /* Reduced top margin */
            overflow-x: auto; /* Add horizontal scroll if needed */
        }
        .button-container, .search-container {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 10px; /* Reduced bottom margin */
        }
        .thumbnail {
            width: 50px;
            height: auto;
            cursor: pointer;
        }
        .action-buttons {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        .btn-small {
            padding: 1px 2px;
            font-size: 8px;
        }
        .btn-delete {
            background-color: red;
            color: white;
        }
    </style>
    <script>
        function promptForId(action) {
            var id = prompt("Please enter the material ID:");
            if (id) {
                if (action === 'edit') {
                    window.location.href = 'edit_material.php?id=' + id;
                } else if (action === 'delete') {
                    if (confirm("Are you sure you want to delete this material?")) {
                        window.location.href = '../../public/materials/delete_material.php?id=' + id;
                    }
                }
            }
        }

        function openImage(url) {
            var newWindow = window.open("", "_blank", "width=600,height=400");
            newWindow.document.write('<html><head><title>Image</title></head><body style="margin:0;padding:0;display:flex;justify-content:center;align-items:center;"><img src="' + url + '" style="max-width:100%;max-height:100%;"><button onclick="window.close()" style="position:absolute;top:10px;right:10px;">Close</button></body></html>');
        }
    </script>
</head>
<body>
    <?php include '../../Views/header.php'; ?>
    <div class="container">
        <h1>Search Materials</h1>
        <form action="../../public/materials/search_materials.php" method="get">
            <input type="text" name="search" placeholder="Search materials..." value="<?php echo htmlspecialchars($searchTerm ?? ''); ?>">
            <input type="submit" value="Search" class="btn styled-btn">
        </form>
    </div>
    <?php if (!empty($materials)): ?>
        <div class="results-container">
            <table class="results-table">
                <thead>
                    <tr>
                        <th>Material Name</th>
                        <th>Length</th>
                        <th>Width</th>
                        <th>Thickness</th>
                        <th>Price</th>
                        <th>Quantity on Hand</th>
                        <th>Type</th>
                        <th>Vendor</th>
                        <th>Item No</th>
                        <th>Item URL</th>
                        <th>Image</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($materials as $material): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($material['material_name']); ?></td>
                            <td><?php echo htmlspecialchars($material['Length']); ?></td>
                            <td><?php echo htmlspecialchars($material['Width']); ?></td>
                            <td><?php echo htmlspecialchars($material['Thickness']); ?></td>
                            <td><?php echo htmlspecialchars($material['Price']); ?></td>
                            <td><?php echo htmlspecialchars($material['Quantity_on_Hand']); ?></td>
                            <td><?php echo htmlspecialchars($material['type']); ?></td>
                            <td><?php echo htmlspecialchars($material['vendor_name'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($material['Item_no']); ?></td>
                            <td><a href="<?php echo htmlspecialchars($material['item_url']); ?>" target="_blank">Link</a></td>
                            <td><img src="<?php echo htmlspecialchars($material['image_url']); ?>" class="thumbnail" alt="Image" onclick="openImage('<?php echo htmlspecialchars($material['image_url']); ?>')"></td>
                            <td class="action-buttons">
                                <button class="btn btn-small styled-btn" onclick="window.location.href='../../views/materials/edit_material.php?id=<?php echo $material['id']; ?>'">Edit</button>
                                <button class="btn btn-small btn-delete" onclick="if(confirm('Are you sure you want to delete this material?')) window.location.href='../../public/materials/delete_material.php?id=<?php echo $material['id']; ?>'">Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p>No materials found.</p>
    <?php endif; ?>
</body>
</html>