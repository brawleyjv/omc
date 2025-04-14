<?php
// Check if config.php has already been included, and include it if missing
if (!defined('BASE_URL')) {
    require_once realpath(dirname(__FILE__) . '/../../config.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Estimate</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>styles.css?v=<?php echo time(); ?>"> <!-- Use BASE_URL for styles -->
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table th, table td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
        }
        table th {
            background-color: #f4f4f4;
        }
    </style>
</head>
<body>
    <?php include BASE_PATH . 'Views/header.php'; ?>
    <div class="container">
        <h1 class="title">Create Estimate</h1>
        <?php if (!empty($errorMessage)): ?>
            <p class="error"><?php echo htmlspecialchars($errorMessage); ?></p>
        <?php endif; ?>

        <!-- Project Search Form -->
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
            <label for="project_search">Search Project:</label>
            <input type="text" id="project_search" name="project_search" placeholder="Enter project name or description">
            <input type="submit" value="Search" class="btn styled-btn">
        </form>

        <!-- Display Matching Projects -->
        <?php if (!empty($matchingProjects)): ?>
            <h3>Matching Projects</h3>
            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
                <table>
                    <thead>
                        <tr>
                            <th>Project Name</th>
                            <th>Description</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($matchingProjects as $project): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($project['project_name']); ?></td>
                                <td><?php echo htmlspecialchars($project['description']); ?></td>
                                <td>
                                    <button type="submit" name="select_project" value="<?php echo htmlspecialchars($project['id']); ?>" class="btn styled-btn">Select</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </form>
        <?php endif; ?>

        <?php if ($projectDetails): ?>
            <h2>Project: <?php echo htmlspecialchars($projectDetails['project_name'] ?? ''); ?></h2>
            <h3>Customer: <?php echo htmlspecialchars($_POST['customer_name'] ?? ''); ?></h3>

            <!-- Main form for saving the estimate -->
            <form action="<?php echo BASE_URL; ?>public/Estimate/save_estimate.php" method="post">
                <input type="hidden" name="project_id" value="<?php echo htmlspecialchars($projectDetails['id'] ?? ''); ?>">

                <!-- Machine and Labor Time Table -->
                <h2>Machine and Labor Time</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Label</th>
                            <th>Imported Time</th>
                            <th>Rate</th>
                            <th>Total Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Router</td>
                            <td><input type="number" name="router_time" value="<?php echo htmlspecialchars($projectDetails['router_time'] ?? 0); ?>" readonly></td>
                            <td>$<?php echo htmlspecialchars($setupRates['mill_rate'] ?? 0); ?></td>
                            <td>$<?php echo number_format(($projectDetails['router_time'] ?? 0) * ($setupRates['mill_rate'] ?? 0), 2); ?></td>
                        </tr>
                        <!-- ...existing rows for Laser, Labor, Customize, Bit Changes... -->
                    </tbody>
                </table>

                <!-- Materials Table -->
                <h2>Materials</h2>
                <table id="materials-table">
                    <thead>
                        <tr>
                            <th>Material</th>
                            <th>Qnty</th>
                            <th>PL</th>
                            <th>PW</th>
                            <th>PT</th>
                            <th>BF</th>
                            <th>Base Cost</th>
                            <th>PMT</th>
                        </tr>
                    </thead>
                    <tbody id="selected-materials">
                        <!-- Selected materials will be added here -->
                    </tbody>
                </table>
            </form>

            <!-- Separate form for searching materials -->
            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) . '?project_id=' . urlencode($projectId); ?>" method="post">
                <label for="material_search">Search Material:</label>
                <input type="text" id="material_search" name="material_search" placeholder="Enter material name">
                <input type="submit" value="Search" class="btn styled-btn">
            </form>

            <!-- Materials Search Results -->
            <?php if (!empty($materials)): ?>
                <h3>Search Results</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Material</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($materials as $material): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($material['material_name'] ?? ''); ?></td>
                                <td>
                                    <button type="button" class="btn styled-btn" onclick="addMaterialToTable(
                                        '<?php echo htmlspecialchars($material['material_name']); ?>',
                                        <?php echo htmlspecialchars($material['price'] ?? 0); ?>,
                                        <?php echo htmlspecialchars($material['length'] ?? 0); ?>,
                                        <?php echo htmlspecialchars($material['width'] ?? 0); ?>,
                                        <?php echo htmlspecialchars($material['thickness'] ?? 0); ?>
                                    )">Select</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No materials found. Use the search above to find materials.</p>
            <?php endif; ?>

            <!-- BOM Details Table -->
            <form action="<?php echo BASE_URL; ?>public/Estimate/save_estimate.php" method="post">
                <h2>BOM Details</h2>
                <table>
                    <!-- ...existing BOM Details table... -->
                </table>

                <input type="submit" value="Save Estimate" class="btn styled-btn">
            </form>
        <?php else: ?>
            <p>No project selected. Use the search above to find and select a project.</p>
        <?php endif; ?>
    </div>

    <script>
        function addMaterialToTable(materialName, price, length, width, thickness) {
            const tableBody = document.getElementById('selected-materials');
            const row = document.createElement('tr');

            // Calculate base_cost
            const baseCost = price / ((length * width * thickness) / 144);

            row.innerHTML = `
                <td>${materialName}</td>
                <td><input type="number" name="materials[${materialName}][quantity]" value="0"></td>
                <td><input type="number" name="materials[${materialName}][pl]" value="0" oninput="updateBFAndPMT(this)"></td>
                <td><input type="number" name="materials[${materialName}][pw]" value="0" oninput="updateBFAndPMT(this)"></td>
                <td><input type="number" name="materials[${materialName}][pt]" value="0" oninput="updateBFAndPMT(this)"></td>
                <td><input type="number" name="materials[${materialName}][bf]" value="0" readonly></td>
                <td><input type="number" name="materials[${materialName}][base_cost]" value="${baseCost.toFixed(2)}" readonly></td>
                <td><input type="number" name="materials[${materialName}][pmt]" value="0" readonly></td>
            `;

            tableBody.appendChild(row);
        }

        function updateBFAndPMT(inputElement) {
            const row = inputElement.closest('tr');
            const pl = parseFloat(row.querySelector('input[name*="[pl]"]').value) || 0;
            const pw = parseFloat(row.querySelector('input[name*="[pw]"]').value) || 0;
            const pt = parseFloat(row.querySelector('input[name*="[pt]"]').value) || 0;
            const baseCost = parseFloat(row.querySelector('input[name*="[base_cost]"]').value) || 0;

            // Calculate BF
            const bf = (pl * pw * pt) / 144;
            row.querySelector('input[name*="[bf]"]').value = bf.toFixed(2);

            // Calculate PMT
            const pmt = bf * baseCost;
            row.querySelector('input[name*="[pmt]"]').value = pmt.toFixed(2);
        }
    </script>
</body>
</html>
