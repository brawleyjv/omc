<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once realpath(dirname(__FILE__) . '/../../config.php');
require_once BASE_PATH . 'Models/Database.php';

use MyApp\Models\Database;

// Get rates from database
$millRate = 0.85;
$laserRate = 0.50;
$laborRate = 25.00;
$bitChangeRate = 5.00;
$customizeRate = 5.00;

try {
    $database = new Database(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $conn = $database->getPdo();
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
    
    // Get existing projects for dropdown
    $projectsQuery = "SELECT DISTINCT project_name FROM projects ORDER BY project_name ASC";
    $projectsStmt = $conn->prepare($projectsQuery);
    $projectsStmt->execute();
    $existingProjects = $projectsStmt->fetchAll(PDO::FETCH_COLUMN);
    
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
    <title>Create New Estimate - OMC</title>
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
        .customer-type-section {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }
        .radio-group {
            display: flex;
            gap: 2rem;
        }
        .radio-option {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        #customer-fields {
            margin-top: 1rem;
        }
    </style>
</head>
<body>
    
    <!-- Modern Header -->
    <header class="header">
        <div class="header-content">
            <div class="header-brand">
                <div class="header-brand-text">
                    <h1>Create New Estimate</h1>
                    <p>Generate professional project estimates from scratch</p>
                </div>
            </div>
            <nav class="header-nav">
                <a href="<?php echo BASE_URL; ?>Views/main.php" class="nav-link">Dashboard</a>
                <a href="<?php echo BASE_URL; ?>Views/estimate/estimate.php" class="nav-link">Estimates Home</a>
                <a href="<?php echo BASE_URL; ?>Views/estimate/list_estimates.php" class="nav-link">All Estimates</a>
                <a href="<?php echo BASE_URL; ?>Views/estimate/create_new_estimate.php" class="nav-link" style="background-color: rgba(255, 255, 255, 0.1);">Create New</a>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-container">
        <form id="estimate-form" action="<?php echo BASE_URL; ?>public/Estimate/save_new_estimate.php" method="post">
            
            <!-- Customer Information -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Customer Information</h2>
                    <p class="card-subtitle">Leave blank for base project estimate (no specific customer)</p>
                </div>
                <div class="card-body">
                    <div class="customer-type-section">
                        <label class="form-label">Estimate Type:</label>
                        <div class="radio-group">
                            <div class="radio-option">
                                <input type="radio" id="type-base" name="estimate_type" value="base" checked onchange="toggleCustomerFields()">
                                <label for="type-base">Base Project (No Customer)</label>
                            </div>
                            <div class="radio-option">
                                <input type="radio" id="type-customer" name="estimate_type" value="customer" onchange="toggleCustomerFields()">
                                <label for="type-customer">Customer-Specific</label>
                            </div>
                        </div>
                    </div>
                    
                    <div id="customer-fields" style="display: none;">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="customer_select" class="form-label">Select Existing Customer</label>
                                <select id="customer_select" class="form-control" onchange="fillCustomerInfo()">
                                    <option value="">-- Select Customer or Enter New --</option>
                                    <?php foreach ($existingCustomers as $customer): ?>
                                        <option value="<?php echo $customer['id']; ?>" 
                                                data-name="<?php echo htmlspecialchars($customer['name'] ?? $customer['customer_name'] ?? ''); ?>"
                                                data-email="<?php echo htmlspecialchars($customer['email'] ?? ''); ?>"
                                                data-phone="<?php echo htmlspecialchars($customer['phone'] ?? ''); ?>"
                                                data-address="<?php echo htmlspecialchars($customer['address'] ?? ''); ?>"
                                                data-city="<?php echo htmlspecialchars($customer['city'] ?? ''); ?>"
                                                data-state="<?php echo htmlspecialchars($customer['state'] ?? ''); ?>"
                                                data-zip="<?php echo htmlspecialchars($customer['zip'] ?? ''); ?>">
                                            <?php echo htmlspecialchars($customer['name'] ?? $customer['customer_name'] ?? ''); ?>
                                            <?php if ($customer['email']): ?>
                                                (<?php echo htmlspecialchars($customer['email']); ?>)
                                            <?php endif; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <h4 style="margin-top: 1.5rem; margin-bottom: 1rem;">Basic Information</h4>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="customer_name" class="form-label">Customer Name *</label>
                                <input type="text" id="customer_name" name="customer_name" class="form-control" placeholder="Enter full customer name">
                            </div>
                        </div>
                        
                        <h4 style="margin-top: 1.5rem; margin-bottom: 1rem;">Contact Information</h4>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="customer_phone" class="form-label">Phone Number</label>
                                <input type="tel" id="customer_phone" name="customer_phone" class="form-control" placeholder="(555) 123-4567">
                            </div>
                            <div class="form-group">
                                <label for="customer_email" class="form-label">Email Address</label>
                                <input type="email" id="customer_email" name="customer_email" class="form-control" placeholder="customer@example.com">
                            </div>
                        </div>
                        
                        <h4 style="margin-top: 1.5rem; margin-bottom: 1rem;">Address Information</h4>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="customer_address" class="form-label">Street Address</label>
                                <input type="text" id="customer_address" name="customer_address" class="form-control" placeholder="123 Main Street">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="customer_city" class="form-label">City</label>
                                <input type="text" id="customer_city" name="customer_city" class="form-control" placeholder="City">
                            </div>
                            <div class="form-group">
                                <label for="customer_state" class="form-label">State</label>
                                <input type="text" id="customer_state" name="customer_state" class="form-control" placeholder="AR" maxlength="2">
                            </div>
                            <div class="form-group">
                                <label for="customer_zip" class="form-label">Zip Code</label>
                                <input type="text" id="customer_zip" name="customer_zip" class="form-control" placeholder="12345" maxlength="5">
                            </div>
                        </div>
                        
                        <h4 style="margin-top: 1.5rem; margin-bottom: 1rem;">Additional Information</h4>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="customer_notes" class="form-label">Notes</label>
                                <textarea id="customer_notes" name="customer_notes" class="form-control" rows="3" placeholder="Add any additional notes about the customer or project..."></textarea>
                                <small class="form-text">Optional notes about the customer or project requirements</small>
                            </div>
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
                            <input type="text" id="project_name" name="project_name" class="form-control" list="existing-projects" required placeholder="Type new name or select existing">
                            <datalist id="existing-projects">
                                <?php foreach ($existingProjects as $projectName): ?>
                                    <option value="<?php echo htmlspecialchars($projectName); ?>">
                                <?php endforeach; ?>
                            </datalist>
                            <small class="form-text">Start typing to see existing projects or enter a new name</small>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="project_description" class="form-label">Project Description</label>
                            <textarea id="project_description" name="project_description" class="form-control" rows="3"></textarea>
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
                            <input type="number" step="0.01" id="router_time" name="router_time" class="form-control" value="0" onchange="calculateTotal()">
                        </div>
                        <div class="form-group">
                            <label for="laser_time" class="form-label">Laser Time (minutes)</label>
                            <input type="number" step="0.01" id="laser_time" name="laser_time" class="form-control" value="0" onchange="calculateTotal()">
                        </div>
                        <div class="form-group">
                            <label for="labor_hours" class="form-label">Labor Hours</label>
                            <input type="number" step="0.01" id="labor_hours" name="labor_hours" class="form-control" value="0" onchange="calculateTotal()">
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
                    <p class="card-subtitle">Hardware, finishing, shipping, etc.</p>
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
                <button type="submit" name="status" value="draft" class="btn btn-secondary">
                    <span class="icon">💾</span> Save as Draft
                </button>
                <button type="submit" name="status" value="sent" class="btn btn-primary">
                    <span class="icon">📧</span> Save & Mark as Sent
                </button>
                <a href="<?php echo BASE_URL; ?>Views/estimate/list_estimates.php" class="btn btn-ghost">
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

            // Rates from database (dynamically loaded from PHP)
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

            // Apply formula: (materials_cost / 0.3) + ((labor_time * hourly_rate) / 0.2)
            const totalLaborTime = laborHours + (routerTime / 60) + (laserTime / 60);
            const materialMarkup = materialsCost / 0.3;
            const laborMarkup = (totalLaborTime * laborRate) / 0.2;
            const totalEstimate = materialMarkup + laborMarkup + bitChangeCost + customizationCost + shippingCost + customItemsCost;

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
        
        // Toggle customer fields based on estimate type
        function toggleCustomerFields() {
            const isCustomerEstimate = document.getElementById('type-customer').checked;
            const customerFields = document.getElementById('customer-fields');
            customerFields.style.display = isCustomerEstimate ? 'block' : 'none';
            
            // Clear customer fields if switching to base project
            if (!isCustomerEstimate) {
                document.getElementById('customer_name').value = '';
                document.getElementById('customer_email').value = '';
                document.getElementById('customer_phone').value = '';
                document.getElementById('customer_select').value = '';
            }
        }
        
        // Fill customer info from dropdown selection
        function fillCustomerInfo() {
            const select = document.getElementById('customer_select');
            const selectedOption = select.options[select.selectedIndex];
            
            if (selectedOption.value) {
                document.getElementById('customer_name').value = selectedOption.getAttribute('data-name') || '';
                document.getElementById('customer_email').value = selectedOption.getAttribute('data-email') || '';
                document.getElementById('customer_phone').value = selectedOption.getAttribute('data-phone') || '';
                document.getElementById('customer_address').value = selectedOption.getAttribute('data-address') || '';
                document.getElementById('customer_city').value = selectedOption.getAttribute('data-city') || '';
                document.getElementById('customer_state').value = selectedOption.getAttribute('data-state') || '';
                document.getElementById('customer_zip').value = selectedOption.getAttribute('data-zip') || '';
            } else {
                // Clear fields if "Select Customer" is chosen
                document.getElementById('customer_name').value = '';
                document.getElementById('customer_email').value = '';
                document.getElementById('customer_phone').value = '';
                document.getElementById('customer_address').value = '';
                document.getElementById('customer_city').value = '';
                document.getElementById('customer_state').value = '';
                document.getElementById('customer_zip').value = '';
            }
        }
    </script>
</body>
</html>