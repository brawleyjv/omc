<?php
require_once realpath(dirname(__FILE__) . '/../../config.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>styles-modern.css">
    <title>Material Search Results</title>
    <script>
        function clearResults() {
            if (confirm('Are you sure you want to clear all search results?')) {
                window.location.href = '<?php echo BASE_URL; ?>Views/materials/search_materials.php';
            }
        }

        function openImage(url) {
            var newWindow = window.open("", "_blank", "width=800,height=600,scrollbars=yes,resizable=yes");
            newWindow.document.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Material Image</title>
                    <style>
                        body { margin: 0; padding: 20px; background: #f5f5f5; display: flex; flex-direction: column; align-items: center; }
                        img { max-width: 100%; max-height: 80vh; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
                        .close-btn { margin-bottom: 20px; padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; }
                        .close-btn:hover { background: #0056b3; }
                    </style>
                </head>
                <body>
                    <button class="close-btn" onclick="window.history.back()">Close</button>
                    <img src="${url}" alt="Material Image" onerror="this.style.display='none'; document.body.innerHTML='<p>Image could not be loaded</p>';">
                </body>
                </html>
            `);
        }
    </script>
</head>
<body>
    <?php include BASE_PATH . '/Views/header.php'; ?>

    <div class="main-content">
        <div class="page-header">
            <div class="header-content">
                <h1>Material Search Results</h1>
                <div class="header-actions">
                    <a href="<?php echo BASE_URL; ?>Views/materials/search_materials.php" class="btn btn-secondary">Back to Search</a>
                    <a href="<?php echo BASE_URL; ?>Views/materials/add_material.php" class="btn btn-primary">Add New Material</a>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2>Search Again</h2>
            </div>
            <div class="card-body">
                <form action="<?php echo BASE_URL; ?>public/materials/search_results.php" method="get" class="search-form">
                    <div class="form-group">
                        <input type="text" 
                               name="search_term" 
                               placeholder="Search for materials..." 
                               value="<?php echo htmlspecialchars($searchTerm ?? ''); ?>"
                               class="form-control">
                    </div>
                    <button type="submit" class="btn btn-primary">Search</button>
                </form>
            </div>
        </div>

        <?php if (!empty($results)): ?>
            <div class="card">
                <div class="card-header">
                    <h2>Search Results</h2>
                    <p class="text-muted">Found <?php echo count($results); ?> materials</p>
                </div>
                <div class="table-container">
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th>Material Name</th>
                                <th>Dimensions</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Type</th>
                                <th>Vendor</th>
                                <th>Item No</th>
                                <th>Links</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($results as $row): ?>
                                <tr>
                                    <td>
                                        <div class="font-semibold"><?php echo htmlspecialchars($row['material_name']); ?></div>
                                    </td>
                                    <td>
                                        <div class="text-sm">
                                            L: <?php echo htmlspecialchars($row['Length']); ?><br>
                                            W: <?php echo htmlspecialchars($row['Width']); ?><br>
                                            T: <?php echo htmlspecialchars($row['Thickness']); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-success">$<?php echo htmlspecialchars($row['Price']); ?></span>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['Quantity_on_Hand']); ?></td>
                                    <td>
                                        <span class="badge badge-info"><?php echo htmlspecialchars($row['type']); ?></span>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['vendor_name'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($row['Item_no']); ?></td>
                                    <td>
                                        <div class="text-sm">
                                            <?php if (!empty($row['item_url'])): ?>
                                                <a href="<?php echo htmlspecialchars($row['item_url']); ?>" target="_blank" class="text-primary">Item Link</a><br>
                                            <?php endif; ?>
                                            <?php if (!empty($row['image_url'])): ?>
                                                <a href="#" onclick="openImage('<?php echo htmlspecialchars($row['image_url']); ?>')" class="text-secondary">View Image</a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="<?php echo BASE_URL; ?>Views/materials/edit_material.php?id=<?php echo $row['id'] ?? ''; ?>" 
                                               class="btn btn-sm btn-secondary">Edit</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php elseif (isset($searchTerm)): ?>
            <div class="card">
                <div class="card-body text-center">
                    <div class="empty-state">
                        <h3>No Materials Found</h3>
                        <p class="text-muted">No materials match your search criteria. Try a different search term.</p>
                        <a href="<?php echo BASE_URL; ?>Views/materials/add_material.php" class="btn btn-primary">Add New Material</a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>