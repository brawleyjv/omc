<?php
require_once __DIR__ . '/../../Globals/Config.php';
require_once __DIR__ . '/../../Models/Database.php';
require_once __DIR__ . '/../../Models/Material.php';
require_once __DIR__ . '/../../Controllers/MaterialController.php';
use MyApp\Controllers\MaterialController;
use MyApp\Models\Database;

$database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS);
$materialsController = new MaterialController($database);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_material_id'])) {
    $materialId = $_POST['delete_material_id'];
    error_log("Deleting material with ID: $materialId"); // Log the material ID being deleted
    $materialsController->deleteMaterial($materialId);
    header('Location: list_materials.php'); // Redirect to refresh the list after deletion
    exit;
}

$materials = $materialsController->getAllMaterials();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>List Materials</title>
    <link rel="stylesheet" href="../../public/css/styles.css">
    <style>
        .top-buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 0px; /* Remove margin to bring the buttons up */
        }
        .center-title {
            text-align: center;
            margin-top: 20px; /* Adjust margin to bring the title up */
        }
        .content {
            margin-top: 0px; /* Remove margin to bring the content up */
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
            border: 2px solid #007BFF; /* Enhance border appearance */
        }
        th, td {
            padding: 8px;
            text-align: center; /* Center the text */
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
            background-color: #DC3545; /* Red background */
            color: white; /* White text */
            padding: 5px 10px; /* Reduce padding */
            font-size: 14px; /* Reduce font size */
            border: none; /* Remove border */
        }
        .btn.styled-btn.red:hover {
            background-color: #c82333; /* Darker red on hover */
        }
        .thumbnail {
            max-width: 100px;
            max-height: 100px;
            cursor: pointer;
        }
        .action-buttons {
            display: flex;
            flex-direction: column;
            gap: 5px; /* Space between buttons */
        }
    </style>
    <script>
        function openImage(url) {
            const imgWindow = window.open("", "_blank", "width=800,height=600");
            imgWindow.document.write(`
                <html>
                <head>
                    <title>Image</title>
                    <style>
                        body { margin: 0; display: flex; justify-content: center; align-items: center; height: 100vh; background-color: #000; }
                        img { max-width: 100%; max-height: 100%; }
                        .close-button {
                            position: absolute;
                            top: 10px;
                            right: 10px;
                            background-color: #DC3545;
                            color: white;
                            border: none;
                            padding: 10px;
                            cursor: pointer;
                            font-size: 16px;
                            border-radius: 5px;
                        }
                        .close-button:hover {
                            background-color: #c82333;
                        }
                    </style>
                </head>
                <body>
                    <button class="close-button" onclick="window.close()">Close</button>
                    <img src="${url}" alt="Material Image">
                </body>
                </html>
            `);
        }

        function handleImageError(img) {
            const link = document.createElement('a');
            link.href = img.src;
            link.target = '_blank';
            link.textContent = 'View Image';
            img.parentNode.replaceChild(link, img);
        }
    </script>
</head>
<body>
    <?php include '../../views/header.php'; ?>
    <h1 class="center-title">List of Materials</h1>
    <div class="top-buttons">
        <button class="btn styled-btn" style="margin-right: 20px;" onclick="window.location.href='index.php'">Close</button>
    </div>
    <div class="content">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
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
                    <th>Image URL</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($materials as $material): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($material['id']); ?></td>
                        <td><?php echo htmlspecialchars($material['material_name'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($material['Length']); ?></td>
                        <td><?php echo htmlspecialchars($material['Width']); ?></td>
                        <td><?php echo htmlspecialchars($material['Thickness']); ?></td>
                        <td><?php echo htmlspecialchars($material['Price']); ?></td>
                        <td><?php echo htmlspecialchars($material['Quantity_on_Hand']); ?></td>
                        <td><?php echo htmlspecialchars($material['type']); ?></td>
                        <td><?php echo htmlspecialchars($material['vendor_name']); ?></td>
                        <td><?php echo htmlspecialchars($material['Item_no']); ?></td>
                        <td><a href="<?php echo htmlspecialchars($material['item_url']); ?>" target="_blank">Link</a></td>
                        <td>
                            <?php if (!empty($material['image_url'])): ?>
                                <img src="<?php echo htmlspecialchars($material['image_url']); ?>" alt="Material Image" class="thumbnail" onclick="openImage('<?php echo htmlspecialchars($material['image_url']); ?>')" onerror="handleImageError(this)">
                            <?php endif; ?>
                        </td>
                        <td class="action-buttons">
                            <button class="btn styled-btn" onclick="window.location.href='edit_material.php?id=<?php echo htmlspecialchars($material['id']); ?>'">Edit</button>
                            <form action="list_materials.php" method="post" onsubmit="return confirm('Are you sure you want to delete this material?');">
                                <input type="hidden" name="delete_material_id" value="<?php echo htmlspecialchars($material['id']); ?>">
                                <input type="submit" class="btn styled-btn red" value="Delete">
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>