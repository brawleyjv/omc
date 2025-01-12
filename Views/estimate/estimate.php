<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estimate</title>
    <link rel="stylesheet" href="/OMC/public/css/styles.css">
</head>
<body>
    <?php include '../../Views/header.php'; ?>
    <h1 class="title">Estimate</h1>
    <div class="container">
        <div class="customer-info">
            <h2>Customer Information</h2>
            <p><strong>Name:</strong> <?php echo $customer_name; ?></p>
            <p><strong>Address:</strong> <?php echo htmlspecialchars($project['customer_address'] ?? ''); ?></p>
            <p><strong>Project Name:</strong> <?php echo $project_name; ?></p>
        </div>
        <div class="bom-list">
            <h2>Bill of Materials</h2>
            <table>
                <thead>
                    <tr>
                        <th>Material ID</th>
                        <th>Material Name</th>
                        <th>Length</th>
                        <th>Width</th>
                        <th>Thickness</th>
                        <th>Quantity</th>
                        <th>Price</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($material_names)): ?>
                        <?php foreach ($material_names as $index => $material_name): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($material_ids[$index]); ?></td>
                                <td><?php echo htmlspecialchars($material_name); ?></td>
                                <td><?php echo htmlspecialchars($lengths[$index]); ?></td>
                                <td><?php echo htmlspecialchars($widths[$index]); ?></td>
                                <td><?php echo htmlspecialchars($thicknesses[$index]); ?></td>
                                <td><?php echo htmlspecialchars($quantities[$index]); ?></td>
                                <td><?php echo htmlspecialchars($prices[$index]); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7">No materials found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
