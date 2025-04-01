<?php
require_once realpath(dirname(__FILE__) . '/../../../config.php'); // Correct relative path to config.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>styles.css"> <!-- Corrected path -->
    <style>
        .close-button {
            position: absolute;
            top: 10px;
            left: 10px;
        }
        .center-title {
            text-align: center;
            margin-top: 20px; /* Add margin to ensure the title is visible below the header */
        }
        .clear-button {
            display: block;
            margin: 20px auto;
        }
    </style>
    <script>
        function clearResults() {
            document.getElementById('results').innerHTML = '';
        }
    </script>
</head>
<body>
    <?php include BASE_PATH . '/Views/header.php'; ?>
    <button class="close-button" onclick="window.location.href='<?php echo BASE_URL; ?>public/materials/search_materials.php'">Close</button>
    <h1 class="center-title">Search Results</h1>
    <form action="<?php echo BASE_URL; ?>public/materials/search_results.php" method="get" style="text-align: center;">
        <input type="text" name="search_term" placeholder="Search for material" value="<?php echo htmlspecialchars($searchTerm); ?>">
        <button type="submit">Search</button>
    </form>
    <div id="results">
        <?php if (!empty($results)): ?>
            <table>
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
                        <th>Image URL</th>
                        <th>Edit</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($results as $row): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['material_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['Length']); ?></td>
                            <td><?php echo htmlspecialchars($row['Width']); ?></td>
                            <td><?php echo htmlspecialchars($row['Thickness']); ?></td>
                            <td><?php echo htmlspecialchars($row['Price']); ?></td>
                            <td><?php echo htmlspecialchars($row['Quantity_on_Hand']); ?></td>
                            <td><?php echo htmlspecialchars($row['type']); ?></td>
                            <td><?php echo htmlspecialchars($row['vendor_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['Item_no']); ?></td>
                            <td><a href="<?php echo htmlspecialchars($row['item_url']); ?>" target="_blank">Link</a></td>
                            <td><a href="<?php echo htmlspecialchars($row['image_url']); ?>" target="_blank">Link</a></td>
                            <td>
                                <form action="<?php echo BASE_URL; ?>Views/materials/edit_material.php" method="get">
                                    <input type="hidden" name="material_name" value="<?php echo htmlspecialchars($row['material_name']); ?>">
                                    <input type="hidden" name="length" value="<?php echo htmlspecialchars($row['Length']); ?>">
                                    <input type="hidden" name="width" value="<?php echo htmlspecialchars($row['Width']); ?>">
                                    <input type="hidden" name="thickness" value="<?php echo htmlspecialchars($row['Thickness']); ?>">
                                    <input type="hidden" name="price" value="<?php echo htmlspecialchars($row['Price']); ?>">
                                    <input type="hidden" name="quantity_on_hand" value="<?php echo htmlspecialchars($row['Quantity_on_Hand']); ?>">
                                    <input type="hidden" name="type" value="<?php echo htmlspecialchars($row['type']); ?>">
                                    <input type="hidden" name="vendor" value="<?php echo htmlspecialchars($row['vendor_name']); ?>">
                                    <input type="hidden" name="item_no" value="<?php echo htmlspecialchars($row['Item_no']); ?>">
                                    <input type="hidden" name="item_url" value="<?php echo htmlspecialchars($row['item_url']); ?>">
                                    <input type="hidden" name="image_url" value="<?php echo htmlspecialchars($row['image_url']); ?>">
                                    <button type="submit">Edit</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No materials found.</p>
        <?php endif; ?>
    </div>
    <button class="clear-button" onclick="clearResults()">Clear Results</button>
</body>
</html>