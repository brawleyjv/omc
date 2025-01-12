<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results</title>
    <link rel="stylesheet" href="../../public/css/styles.css">
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

        function confirmEdit(materialId) {
            if (confirm('Do you want to edit the material with ID ' + materialId + '?')) {
                window.location.href = 'edit.php?material_id=' + materialId;
            }
        }

        window.onload = function() {
            if (<?php echo json_encode($noResults); ?>) {
                alert('No materials found.');
            }
        }
    </script>
</head>
<body>
    <?php include '../../views/header.php'; ?>
    <button class="close-button" onclick="window.location.href='search_materials.php'">Close</button>
    <h1 class="center-title">Search Results</h1>
    <form action="../../public/materials/search_results.php" method="get" style="text-align: center;">
        <input type="text" name="search_term" placeholder="Search for material" value="<?php echo htmlspecialchars($search_term); ?>">
        <button type="submit">Search</button>
    </form>
    <div id="results">
        <?php if (!empty($results)): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Description</th>
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
                            <td><?php echo htmlspecialchars($row['id']); ?></td>
                            <td><?php echo htmlspecialchars($row['Description']); ?></td>
                            <td><?php echo htmlspecialchars($row['Length']); ?></td>
                            <td><?php echo htmlspecialchars($row['Width']); ?></td>
                            <td><?php echo htmlspecialchars($row['Thickness']); ?></td>
                            <td><?php echo htmlspecialchars($row['Price']); ?></td>
                            <td><?php echo htmlspecialchars($row['Quantity_on_Hand']); ?></td>
                            <td><?php echo htmlspecialchars($row['type']); ?></td>
                            <td><?php echo htmlspecialchars($row['vendor']); ?></td>
                            <td><?php echo htmlspecialchars($row['Item_no']); ?></td>
                            <td><?php echo htmlspecialchars($row['item_url']); ?></td>
                            <td><?php echo htmlspecialchars($row['image_url']); ?></td>
                            <td><button onclick="confirmEdit(<?php echo $row['id']; ?>)">Edit</button></td>
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