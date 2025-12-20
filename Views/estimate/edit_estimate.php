<?php
require_once realpath(dirname(__FILE__) . '/../../config.php');
require_once BASE_PATH . 'Models/Database.php';
require_once BASE_PATH . 'Models/EstimateModel.php';

use MyApp\Models\Database;

// Get estimate by ID
$estimateId = $_GET['id'] ?? null;
$isCloning = isset($_GET['clone']);
$estimate = null;

if ($estimateId) {
    try {
        $database = new Database(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
        $conn = $database->getPdo();
        $estimateModel = new EstimateModel($conn);
        $estimate = $estimateModel->getEstimateById($estimateId);
        
        // Get existing customers for dropdown
        $customersQuery = "SELECT id, customer_name, email, phone FROM customers ORDER BY customer_name ASC";
        $customersStmt = $conn->prepare($customersQuery);
        $customersStmt->execute();
        $existingCustomers = $customersStmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (Exception $e) {
        error_log("Error loading estimate: " . $e->getMessage());
    }
}

if (!$estimate) {
    header("Location: " . BASE_URL . "Views/estimate/list_estimates.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Estimate - OMC</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>styles-modern.css">
    <style>
        .material-row, .custom-item-row {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr 1fr 0.5fr;
            gap: 1rem;
            align-items: end;
            margin-bottom: 1rem;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 8px;
        }
        .remove-btn {
            background: #dc3545;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            cursor: pointer;
        }
        .calculation-summary {
            background: #e7f3ff;
            padding: 1.5rem;
            border-radius: 8px;
            margin-top: 2rem;
        }
        .calculation-row {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            border-bottom: 1px solid #ccc;
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-content">
            <div class="header-brand">
                <div class="header-brand-text">
                    <h1>Edit Estimate</h1>
                    <p><?php echo htmlspecialchars($estimate['estimate_number']); ?></p>
                </div>
            </div>
            <nav class="header-nav">
                <a href="<?php echo BASE_URL; ?>Views/main.php" class="nav-link">Dashboard</a>
                <a href="<?php echo BASE_URL; ?>Views/estimate/estimate.php" class="nav-link">Estimates Home</a>
                <a href="<?php echo BASE_URL; ?>Views/estimate/list_estimates.php" class="nav-link">All Estimates</a>
                <a href="<?php echo BASE_URL; ?>Views/estimate/create_new_estimate.php" class="nav-link">Create New</a>
            </nav>
        </div>
    </header>

    <main class="main-container">
        <form id="estimate-form" action="<?php echo BASE_URL; ?>public/Estimate/update_estimate.php" method="post">
            <input type="hidden" name="estimate_id" value="<?php echo $estimate['id']; ?>">
            
            <!-- Customer Information -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Customer Information</h2>
                </div>
                <div class="card-body">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="customer_name" class="form-label">Customer Name *</label>
                            <input type="text" id="customer_name" name="customer_name" class="form-control" 
                                   value="<?php echo htmlspecialchars($estimate['customer_name']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="customer_email" class="form-label">Email</label>
                            <input type="email" id="customer_email" name="customer_email" class="form-control"
                                   value="<?php echo htmlspecialchars($estimate['customer_email'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label for="customer_phone" class="form-label">Phone</label>
                            <input type="tel" id="customer_phone" name="customer_phone" class="form-control"
                                   value="<?php echo htmlspecialchars($estimate['customer_phone'] ?? ''); ?>">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Project Information -->
            <div class="card mt-4">
                <div class="card-header">
                    <h2 class="card-title">Project Information</h2>
                </div>
                <div class="card-body">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="project_name" class="form-label">Project Name *</label>
                            <input type="text" id="project_name" name="project_name" class="form-control" 
                                   value="<?php echo htmlspecialchars($estimate['project_name']); ?>" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="project_description" class="form-label">Project Description</label>
                            <textarea id="project_description" name="project_description" class="form-control" rows="3"><?php echo htmlspecialchars($estimate['project_description'] ?? ''); ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Time Estimates -->
            <div class="card mt-4">
                <div class="card-header">
                    <h2 class="card-title">Time Estimates</h2>
                </div>
                <div class="card-body">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="router_time" class="form-label">Router Time (minutes)</label>
                            <input type="number" step="0.01" id="router_time" name="router_time" class="form-control" 
                                   value="<?php echo $estimate['router_time']; ?>" onchange="calculateTotal()">
                        </div>
                        <div class="form-group">
                            <label for="laser_time" class="form-label">Laser Time (minutes)</label>
                            <input type="number" step="0.01" id="laser_time" name="laser_time" class="form-control" 
                                   value="<?php echo $estimate['laser_time']; ?>" onchange="calculateTotal()">
                        </div>
                        <div class="form-group">
                            <label for="labor_hours" class="form-label">Labor Hours</label>
                            <input type="number" step="0.01" id="labor_hours" name="labor_hours" class="form-control" 
                                   value="<?php echo $estimate['labor_hours']; ?>" onchange="calculateTotal()">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Materials -->
            <div class="card mt-4">
                <div class="card-header">
                    <h2 class="card-title">Materials</h2>
                </div>
                <div class="card-body">
                    <div id="materials-container">
                        <?php foreach ($estimate['materials'] as $index => $material): ?>
                        <div class="material-row" id="material-<?php echo $index; ?>">
                            <div class="form-group">
                                <label class="form-label">Material Name</label>
                                <input type="text" name="material_name[]" class="form-control" 
                                       value="<?php echo htmlspecialchars($material['material_name']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Quantity</label>
                                <input type="number" step="0.01" name="material_quantity[]" class="form-control material-quantity" 
                                       value="<?php echo $material['quantity']; ?>" required onchange="calculateMaterialTotal(<?php echo $index; ?>)">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Unit Type</label>
                                <select name="material_unit_type[]" class="form-control">
                                    <option value="piece" <?php echo $material['unit_type'] == 'piece' ? 'selected' : ''; ?>>Piece</option>
                                    <option value="sheet" <?php echo $material['unit_type'] == 'sheet' ? 'selected' : ''; ?>>Sheet</option>
                                    <option value="board_foot" <?php echo $material['unit_type'] == 'board_foot' ? 'selected' : ''; ?>>Board Foot</option>
                                    <option value="linear_foot" <?php echo $material['unit_type'] == 'linear_foot' ? 'selected' : ''; ?>>Linear Foot</option>
                                    <option value="square_foot" <?php echo $material['unit_type'] == 'square_foot' ? 'selected' : ''; ?>>Square Foot</option>
                                    <option value="other" <?php echo $material['unit_type'] == 'other' ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Unit Cost ($)</label>
                                <input type="number" step="0.01" name="material_unit_cost[]" class="form-control material-unit-cost" 
                                       value="<?php echo $material['unit_cost']; ?>" required onchange="calculateMaterialTotal(<?php echo $index; ?>)">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Total ($)</label>
                                <input type="number" step="0.01" name="material_total_cost[]" class="form-control material-total-cost" 
                                       id="material-total-<?php echo $index; ?>" value="<?php echo $material['total_cost']; ?>" readonly>
                            </div>
                            <div class="form-group">
                                <button type="button" class="remove-btn" onclick="removeMaterialRow(<?php echo $index; ?>)">✖️</button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" onclick="addMaterialRow()" class="btn btn-secondary mt-3">
                        <span class="icon">➕</span> Add Material
                    </button>
                </div>
            </div>

            <!-- Custom Items -->
            <div class="card mt-4">
                <div class="card-header">
                    <h2 class="card-title">Additional Items</h2>
                </div>
                <div class="card-body">
                    <div id="custom-items-container">
                        <?php foreach ($estimate['custom_items'] as $index => $item): ?>
                        <div class="custom-item-row" id="custom-item-<?php echo $index; ?>">
                            <div class="form-group" style="grid-column: span 3;">
                                <label class="form-label">Item Name</label>
                                <input type="text" name="custom_item_name[]" class="form-control" 
                                       value="<?php echo htmlspecialchars($item['item_name']); ?>" required>
                            </div>
                            <div class="form-group" style="grid-column: span 2;">
                                <label class="form-label">Cost ($)</label>
                                <input type="number" step="0.01" name="custom_item_cost[]" class="form-control custom-item-cost" 
                                       value="<?php echo $item['cost']; ?>" required onchange="calculateTotal()">
                            </div>
                            <div class="form-group">
                                <button type="button" class="remove-btn" onclick="removeCustomItemRow(<?php echo $index; ?>)">✖️</button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" onclick="addCustomItemRow()" class="btn btn-secondary mt-3">
                        <span class="icon">➕</span> Add Item
                    </button>
                </div>
            </div>

            <!-- Calculation Summary -->
            <div class="card mt-4 calculation-summary">
                <h3>Estimate Summary</h3>
                <div class="calculation-row">
                    <span>Total Estimate (with markup):</span>
                    <span id="display-total" style="font-size: 1.5rem; font-weight: bold; color: #0066cc;">$<?php echo number_format($estimate['total_estimate'], 2); ?></span>
                </div>
            </div>

            <!-- Notes -->
            <div class="card mt-4">
                <div class="card-header">
                    <h2 class="card-title">Internal Notes</h2>
                </div>
                <div class="card-body">
                    <textarea id="notes" name="notes" class="form-control" rows="3"><?php echo htmlspecialchars($estimate['notes'] ?? ''); ?></textarea>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="form-actions mt-4">
                <button type="submit" class="btn btn-primary">
                    <span class="icon">💾</span> Update Estimate
                </button>
                <a href="<?php echo BASE_URL; ?>Views/estimate/view_estimate.php?id=<?php echo $estimate['id']; ?>" class="btn btn-secondary">
                    <span class="icon">✖️</span> Cancel
                </a>
            </div>
        </form>
    </main>

    <script>
        let materialCount = <?php echo count($estimate['materials']); ?>;
        let customItemCount = <?php echo count($estimate['custom_items']); ?>;

        function addMaterialRow() {
            materialCount++;
            const container = document.getElementById('materials-container');
            const row = document.createElement('div');
            row.className = 'material-row';
            row.id = `material-${materialCount}`;
            row.innerHTML = `
                <div class="form-group">
                    <label class="form-label">Material Name</label>
                    <input type="text" name="material_name[]" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Quantity</label>
                    <input type="number" step="0.01" name="material_quantity[]" class="form-control material-quantity" required onchange="calculateMaterialTotal(${materialCount})">
                </div>
                <div class="form-group">
                    <label class="form-label">Unit Type</label>
                    <select name="material_unit_type[]" class="form-control">
                        <option value="piece">Piece</option>
                        <option value="sheet">Sheet</option>
                        <option value="board_foot">Board Foot</option>
                        <option value="linear_foot">Linear Foot</option>
                        <option value="square_foot">Square Foot</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Unit Cost ($)</label>
                    <input type="number" step="0.01" name="material_unit_cost[]" class="form-control material-unit-cost" required onchange="calculateMaterialTotal(${materialCount})">
                </div>
                <div class="form-group">
                    <label class="form-label">Total ($)</label>
                    <input type="number" step="0.01" name="material_total_cost[]" class="form-control material-total-cost" id="material-total-${materialCount}" readonly>
                </div>
                <div class="form-group">
                    <button type="button" class="remove-btn" onclick="removeMaterialRow(${materialCount})">✖️</button>
                </div>
            `;
            container.appendChild(row);
        }

        function calculateMaterialTotal(id) {
            const row = document.getElementById(`material-${id}`);
            if (!row) return;
            const quantity = parseFloat(row.querySelector('.material-quantity').value) || 0;
            const unitCost = parseFloat(row.querySelector('.material-unit-cost').value) || 0;
            const total = quantity * unitCost;
            row.querySelector('.material-total-cost').value = total.toFixed(2);
            calculateTotal();
        }

        function removeMaterialRow(id) {
            document.getElementById(`material-${id}`).remove();
            calculateTotal();
        }

        function addCustomItemRow() {
            customItemCount++;
            const container = document.getElementById('custom-items-container');
            const row = document.createElement('div');
            row.className = 'custom-item-row';
            row.id = `custom-item-${customItemCount}`;
            row.innerHTML = `
                <div class="form-group" style="grid-column: span 3;">
                    <label class="form-label">Item Name</label>
                    <input type="text" name="custom_item_name[]" class="form-control" required>
                </div>
                <div class="form-group" style="grid-column: span 2;">
                    <label class="form-label">Cost ($)</label>
                    <input type="number" step="0.01" name="custom_item_cost[]" class="form-control custom-item-cost" required onchange="calculateTotal()">
                </div>
                <div class="form-group">
                    <button type="button" class="remove-btn" onclick="removeCustomItemRow(${customItemCount})">✖️</button>
                </div>
            `;
            container.appendChild(row);
        }

        function removeCustomItemRow(id) {
            document.getElementById(`custom-item-${id}`).remove();
            calculateTotal();
        }

        function calculateTotal() {
            let materialsCost = 0;
            document.querySelectorAll('.material-total-cost').forEach(input => {
                materialsCost += parseFloat(input.value) || 0;
            });

            let customItemsCost = 0;
            document.querySelectorAll('.custom-item-cost').forEach(input => {
                customItemsCost += parseFloat(input.value) || 0;
            });

            const routerTime = parseFloat(document.getElementById('router_time').value) || 0;
            const laserTime = parseFloat(document.getElementById('laser_time').value) || 0;
            const laborHours = parseFloat(document.getElementById('labor_hours').value) || 0;

            const laborRate = 25.00;
            const totalLaborTime = laborHours + (routerTime / 60) + (laserTime / 60);
            const materialMarkup = materialsCost / 0.3;
            const laborMarkup = (totalLaborTime * laborRate) / 0.2;
            const totalEstimate = materialMarkup + laborMarkup + customItemsCost;

            document.getElementById('display-total').textContent = '$' + totalEstimate.toFixed(2);
        }

        // Initialize calculations
        window.onload = function() {
            calculateTotal();
        };
    </script>
</body>
</html>