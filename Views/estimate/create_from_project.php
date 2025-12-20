<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once realpath(dirname(__FILE__) . '/../../config.php');
require_once BASE_PATH . 'Models/Database.php';
require_once BASE_PATH . '/Models/ProjectModel.php';

use MyApp\Models\Database;
use MyApp\Models\ProjectModel;

// Get project_id from URL
$projectId = $_GET['project_id'] ?? null;

if (!$projectId) {
    header("Location: " . BASE_URL . "Views/projects/list_projects.php?error=No project specified");
    exit;
}

// Get rates from database
$millRate = 0.85;
$laserRate = 0.50;
$laborRate = 25.00;
$bitChangeRate = 5.00;
$customizeRate = 5.00;

try {
    $database = new Database(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $conn = $database->getPdo();
    
    // Fetch project data
    $projectModel = new ProjectModel($conn);
    $project = $projectModel->getProjectById($projectId);
    
    if (!$project) {
        header("Location: " . BASE_URL . "Views/projects/list_projects.php?error=Project not found");
        exit;
    }
    
    // Check if project already has an estimate
    if (!empty($project['estimate_id'])) {
        header("Location: " . BASE_URL . "Views/estimate/view_estimate.php?id=" . $project['estimate_id']);
        exit;
    }
    
    // Get rates
    $ratesQuery = "SELECT mill_rate, laser_rate, labor_rate, bit_change_rate, customize_rate FROM setup LIMIT 1";
    $ratesStmt = $conn->prepare($ratesQuery);
    $ratesStmt->execute();
    $rates = $ratesStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($rates) {
        $millRate = $rates['mill_rate'];
        $laserRate = $rates['laser_rate'];
        $laborRate = $rates['labor_rate'];
        $bitChangeRate = $rates['bit_change_rate'];
        $customizeRate = $rates['customize_rate'];
    }
    
    // Get existing customers for dropdown
    $customersQuery = "SELECT id, name, email, phone, address, city, state, zip FROM customers ORDER BY name ASC";
    $customersStmt = $conn->prepare($customersQuery);
    $customersStmt->execute();
    $existingCustomers = $customersStmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    error_log("Error loading data: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Estimate from Project - OMC</title>
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
        .remove-btn:hover {
            background: #c82333;
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
        .calculation-row:last-child {
            border-bottom: none;
            font-weight: bold;
            font-size: 1.2rem;
            color: #0066cc;
        }
        .project-info-banner {
            background: #d4edda;
            border: 2px solid #28a745;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    
    <!-- Modern Header -->
    <header class="header">
        <div class="header-content">
            <div class="header-brand">
                <div class="header-brand-text">
                    <h1>Create Estimate from Project</h1>
                    <p>Generate estimate for: <?php echo htmlspecialchars($project['project_name']); ?></p>
                </div>
            </div>
            <nav class="header-nav">
                <a href="<?php echo BASE_URL; ?>Views/main.php" class="nav-link">Dashboard</a>
                <a href="<?php echo BASE_URL; ?>Views/projects/list_projects.php" class="nav-link">Projects</a>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-container">
        <form id="estimate-form" action="<?php echo BASE_URL; ?>public/Estimate/save_from_project.php" method="post">
            <input type="hidden" name="project_id" value="<?php echo $projectId; ?>">
            <input type="hidden" name="project_name" value="<?php echo htmlspecialchars($project['project_name']); ?>">
            <input type="hidden" name="project_description" value="<?php echo htmlspecialchars($project['project_description'] ?? ''); ?>">
            <input type="hidden" name="router_time_preset" value="<?php echo $project['router_time']; ?>">
            <input type="hidden" name="laser_time_preset" value="<?php echo $project['laser_time']; ?>">
            <input type="hidden" name="labor_hours_preset" value="<?php echo $project['labor_hours']; ?>">
            
            <!-- Project Info Banner -->
            <div class="project-info-banner">
                <strong>📋 Creating estimate for project:</strong> <?php echo htmlspecialchars($project['project_name']); ?>
                <br><small>Time estimates pre-filled from project: Router: <?php echo $project['router_time']; ?>min, Laser: <?php echo $project['laser_time']; ?>min, Labor: <?php echo $project['labor_hours']; ?>hrs</small>
            </div>
            
            <!-- Customer Information -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Customer Information</h2>
                    <p class="card-subtitle">Optional - Leave blank for base project estimate</p>
                </div>
                <div class="card-body">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="customer_select" class="form-label">Select Existing Customer (Optional)</label>
                            <select id="customer_select" class="form-control" onchange="fillCustomerInfo()">
                                <option value="">-- Leave Blank or Select Customer --</option>
                                <?php foreach ($existingCustomers as $customer): ?>
                                    <option value="<?php echo $customer['id']; ?>" 
                                            data-name="<?php echo htmlspecialchars($customer['name']); ?>"
                                            data-email="<?php echo htmlspecialchars($customer['email'] ?? ''); ?>"
                                            data-phone="<?php echo htmlspecialchars($customer['phone'] ?? ''); ?>">
                                        <?php echo htmlspecialchars($customer['name']); ?>
                                        <?php if ($customer['email']): ?>
                                            (<?php echo htmlspecialchars($customer['email']); ?>)
                                        <?php endif; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="customer_name" class="form-label">Customer Name</label>
                            <input type="text" id="customer_name" name="customer_name" class="form-control" placeholder="Enter customer name or leave blank" value="<?php echo htmlspecialchars($project['customer_name'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label for="customer_email" class="form-label">Email</label>
                            <input type="email" id="customer_email" name="customer_email" class="form-control" placeholder="customer@example.com">
                        </div>
                        <div class="form-group">
                            <label for="customer_phone" class="form-label">Phone</label>
                            <input type="tel" id="customer_phone" name="customer_phone" class="form-control" placeholder="(555) 123-4567">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Project Information (Pre-filled from project) -->
            <div class="card mt-4">
                <div class="card-header">
                    <h2 class="card-title">Project Information</h2>
                    <p class="card-subtitle">Pre-filled from project data</p>
                </div>
                <div class="card-body">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Project Name</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($project['project_name']); ?>" readonly style="background: #e9ecef;">
                        </div>
                    </div>
                    <?php if (!empty($project['project_description'])): ?>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Project Description</label>
                            <textarea class="form-control" rows="3" readonly style="background: #e9ecef;"><?php echo htmlspecialchars($project['project_description']); ?></textarea>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Time Estimates (Pre-filled) -->
            <div class="card mt-4">
                <div class="card-header">
                    <h2 class="card-title">Time Estimates</h2>
                    <p class="card-subtitle">Pre-filled from project, can be adjusted</p>
                </div>
                <div class="card-body">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="router_time" class="form-label">Router Time (minutes)</label>
                            <input type="number" step="0.01" id="router_time" name="router_time" class="form-control" value="<?php echo $project['router_time']; ?>" onchange="calculateTotal()">
                        </div>
                        <div class="form-group">
                            <label for="laser_time" class="form-label">Laser Time (minutes)</label>
                            <input type="number" step="0.01" id="laser_time" name="laser_time" class="form-control" value="<?php echo $project['laser_time']; ?>" onchange="calculateTotal()">
                        </div>
                        <div class="form-group">
                            <label for="labor_hours" class="form-label">Labor Hours</label>
                            <input type="number" step="0.01" id="labor_hours" name="labor_hours" class="form-control" value="<?php echo $project['labor_hours']; ?>" onchange="calculateTotal()">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="bit_changes" class="form-label">Number of Bit Changes</label>
                            <input type="number" step="1" id="bit_changes" name="bit_changes" class="form-control" value="0" onchange="calculateTotal()">
                            <small class="form-text">@ $<?php echo number_format($bitChangeRate, 2); ?> per change</small>
                        </div>
                        <div class="form-group">
                            <label for="needs_customization" class="form-label">Customization Required</label>
                            <select id="needs_customization" name="needs_customization" class="form-control" onchange="calculateTotal()">
                                <option value="0">No</option>
                                <option value="1">Yes (+$<?php echo number_format($customizeRate, 2); ?>)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="shipping_cost" class="form-label">Shipping/Packaging Cost ($)</label>
                            <input type="number" step="0.01" id="shipping_cost" name="shipping_cost" class="form-control" value="0" onchange="calculateTotal()">
                            <small class="form-text">Enter estimated shipping cost</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Materials -->
            <div class="card mt-4">
                <div class="card-header">
                    <h2 class="card-title">Materials</h2>
                    <p class="card-subtitle">Add materials with quantities and costs</p>
                </div>
                <div class="card-body">
                    <div id="materials-container">
                        <!-- Material rows will be added here -->
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
                    <p class="card-subtitle">Hardware, finishing, etc.</p>
                </div>
                <div class="card-body">
                    <div id="custom-items-container">
                        <!-- Custom item rows will be added here -->
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
                    <span>Materials Cost:</span>
                    <span id="display-materials-cost">$0.00</span>
                </div>
                <div class="calculation-row">
                    <span>Machine Cost (Router + Laser):</span>
                    <span id="display-machine-cost">$0.00</span>
                </div>
                <div class="calculation-row">
                    <span>Labor Cost:</span>
                    <span id="display-labor-cost">$0.00</span>
                </div>
                <div class="calculation-row">
                    <span>Bit Changes:</span>
                    <span id="display-bit-changes">$0.00</span>
                </div>
                <div class="calculation-row">
                    <span>Customization:</span>
                    <span id="display-customization">$0.00</span>
                </div>
                <div class="calculation-row">
                    <span>Shipping/Packaging:</span>
                    <span id="display-shipping">$0.00</span>
                </div>
                <div class="calculation-row">
                    <span>Additional Items:</span>
                    <span id="display-custom-cost">$0.00</span>
                </div>
                <div class="calculation-row">
                    <span>Subtotal:</span>
                    <span id="display-subtotal">$0.00</span>
                </div>
                <div class="calculation-row">
                    <span><strong>Total Estimate (with markup):</strong></span>
                    <span id="display-total">$0.00</span>
                </div>
            </div>

            <!-- Notes -->
            <div class="card mt-4">
                <div class="card-header">
                    <h2 class="card-title">Internal Notes</h2>
                </div>
                <div class="card-body">
                    <textarea id="notes" name="notes" class="form-control" rows="3" placeholder="Internal notes (not shown to customer)"></textarea>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="form-actions mt-4">
                <button type="submit" name="status" value="draft" class="btn btn-primary">
                    <span class="icon">💰</span> Create Estimate
                </button>
                <a href="<?php echo BASE_URL; ?>Views/projects/list_projects.php" class="btn btn-ghost">
                    <span class="icon">✖️</span> Cancel
                </a>
            </div>
        </form>
    </main>

    <script>
        let materialCount = 0;
        let customItemCount = 0;

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
            // Get all material totals
            let materialsCost = 0;
            document.querySelectorAll('.material-total-cost').forEach(input => {
                materialsCost += parseFloat(input.value) || 0;
            });

            // Get all custom items
            let customItemsCost = 0;
            document.querySelectorAll('.custom-item-cost').forEach(input => {
                customItemsCost += parseFloat(input.value) || 0;
            });

            // Get time values
            const routerTime = parseFloat(document.getElementById('router_time').value) || 0;
            const laserTime = parseFloat(document.getElementById('laser_time').value) || 0;
            const laborHours = parseFloat(document.getElementById('labor_hours').value) || 0;
            const bitChanges = parseFloat(document.getElementById('bit_changes').value) || 0;
            const needsCustomization = parseFloat(document.getElementById('needs_customization').value) || 0;
            const shippingCost = parseFloat(document.getElementById('shipping_cost').value) || 0;

            // Rates from database
            const millRate = <?php echo $millRate; ?>;
            const laserRate = <?php echo $laserRate; ?>;
            const laborRate = <?php echo $laborRate; ?>;
            const bitChangeRate = <?php echo $bitChangeRate; ?>;
            const customizeRate = <?php echo $customizeRate; ?>;

            // Calculate machine and labor costs
            const machineCost = (routerTime * millRate) + (laserTime * laserRate);
            const laborCost = laborHours * laborRate;
            const bitChangeCost = bitChanges * bitChangeRate;
            const customizationCost = needsCustomization ? customizeRate : 0;

            // Calculate subtotal
            const subtotal = materialsCost + machineCost + laborCost + bitChangeCost + customizationCost + shippingCost + customItemsCost;

            // Apply formula: (materials_cost / 0.3) + ((labor_hours * hourly_rate) / 0.2) + machine_cost (no markup) + other items (no markup)
            const materialMarkup = materialsCost / 0.3;
            const laborMarkup = (laborHours * laborRate) / 0.2;
            const totalEstimate = materialMarkup + laborMarkup + machineCost + bitChangeCost + customizationCost + shippingCost + customItemsCost;

            // Update display
            document.getElementById('display-materials-cost').textContent = '$' + materialsCost.toFixed(2);
            document.getElementById('display-machine-cost').textContent = '$' + machineCost.toFixed(2);
            document.getElementById('display-labor-cost').textContent = '$' + laborCost.toFixed(2);
            document.getElementById('display-bit-changes').textContent = '$' + bitChangeCost.toFixed(2);
            document.getElementById('display-customization').textContent = '$' + customizationCost.toFixed(2);
            document.getElementById('display-shipping').textContent = '$' + shippingCost.toFixed(2);
            document.getElementById('display-custom-cost').textContent = '$' + customItemsCost.toFixed(2);
            document.getElementById('display-subtotal').textContent = '$' + subtotal.toFixed(2);
            document.getElementById('display-total').textContent = '$' + totalEstimate.toFixed(2);
        }

        // Add first material row on load
        window.onload = function() {
            addMaterialRow();
        };
        
        // Fill customer info from dropdown selection
        function fillCustomerInfo() {
            const select = document.getElementById('customer_select');
            const selectedOption = select.options[select.selectedIndex];
            
            if (selectedOption.value) {
                document.getElementById('customer_name').value = selectedOption.getAttribute('data-name') || '';
                document.getElementById('customer_email').value = selectedOption.getAttribute('data-email') || '';
                document.getElementById('customer_phone').value = selectedOption.getAttribute('data-phone') || '';
            } else {
                // Clear fields if "Leave Blank" is chosen
                document.getElementById('customer_name').value = '<?php echo htmlspecialchars($project['customer_name'] ?? ''); ?>';
                document.getElementById('customer_email').value = '';
                document.getElementById('customer_phone').value = '';
            }
        }
    </script>
</body>
</html>
