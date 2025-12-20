<?php
/**
 * Etsy Product Linking - Link Etsy products to OMC projects
 * 
 * This page allows you to:
 * - View all products sold on Etsy (from order items)
 * - Link unlinked products to OMC projects
 * - Create permanent mappings for auto-matching
 * - View sales statistics per product
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../Models/Database.php';
require_once __DIR__ . '/../../Models/EtsyOrderParser.php';
require_once __DIR__ . '/../../Models/ProjectModel.php';

use MyApp\Models\Database;
use MyApp\Models\EtsyOrderParser;
use MyApp\Models\ProjectModel;

// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check authentication
if (!isset($_SESSION['username'])) {
    header("Location: " . BASE_URL . "login.php");
    exit();
}

// Initialize database
$database = new Database();
$db = $database->getPdo();
$parser = new EtsyOrderParser($db);
$projectModel = new ProjectModel($db);

// Handle form submission (linking a product)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'create_mapping') {
        $result = $parser->createProductMapping([
            'etsy_listing_id' => $_POST['listing_id'] ?? null,
            'product_name' => $_POST['product_name'],
            'product_sku' => $_POST['product_sku'] ?? null,
            'project_id' => $_POST['project_id'],
            'match_type' => $_POST['match_type'],
            'confidence' => 1.00,
            'active' => true
        ]);
        
        if ($result) {
            $_SESSION['success_message'] = 'Product mapping created successfully! Future orders will auto-match.';
        } else {
            $_SESSION['error_message'] = 'Failed to create product mapping.';
        }
        
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
}

// Get unlinked products
$unlinkedProducts = $parser->getUnlinkedProducts();

// Get all projects for dropdown
$projects = $projectModel->getAllProjects();

// Get existing mappings
$mappingsQuery = "SELECT 
                    epm.*,
                    p.project_name
                  FROM etsy_product_mappings epm
                  INNER JOIN projects p ON epm.project_id = p.id
                  WHERE epm.active = TRUE
                  ORDER BY epm.times_matched DESC, epm.created_at DESC";
$mappingsStmt = $db->prepare($mappingsQuery);
$mappingsStmt->execute();
$existingMappings = $mappingsStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Link Etsy Products - OMC</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>styles-modern.css">
    <style>
        .product-card {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            background: white;
        }
        .product-card.linked {
            border-left: 4px solid #10b981;
            background: #f0fdf4;
        }
        .product-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 1rem;
            margin: 1rem 0;
        }
        .stat {
            text-align: center;
        }
        .stat-value {
            font-size: 1.5rem;
            font-weight: 600;
            color: #1f2937;
        }
        .stat-label {
            font-size: 0.875rem;
            color: #6b7280;
        }
        .mapping-form {
            background: #f9fafb;
            padding: 1rem;
            border-radius: 6px;
            margin-top: 1rem;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-content">
            <div class="header-brand">
                <div class="header-brand-text">
                    <h1>🔗 Link Etsy Products</h1>
                    <p>Connect Etsy products to your OMC projects for better tracking</p>
                </div>
            </div>
            <nav class="header-nav">
                <a href="<?php echo BASE_URL; ?>public/etsy/dashboard.php" class="nav-link">Etsy Dashboard</a>
                <a href="<?php echo BASE_URL; ?>Views/main.php" class="nav-link">Main Dashboard</a>
            </nav>
        </div>
    </header>

    <main class="main-container">
        <!-- Success/Error Messages -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="notification notification-success mb-4">
                ✅ <?php echo htmlspecialchars($_SESSION['success_message']); ?>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="notification notification-error mb-4">
                ❌ <?php echo htmlspecialchars($_SESSION['error_message']); ?>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>

        <!-- Overview Card -->
        <div class="card mb-4">
            <div class="card-header">
                <h2 class="card-title">Product Linking Overview</h2>
                <p class="card-subtitle">Link Etsy products to projects for automatic matching and better estimates</p>
            </div>
            <div class="card-body">
                <div class="product-stats">
                    <div class="stat">
                        <div class="stat-value"><?php echo count($unlinkedProducts); ?></div>
                        <div class="stat-label">Unlinked Products</div>
                    </div>
                    <div class="stat">
                        <div class="stat-value"><?php echo count($existingMappings); ?></div>
                        <div class="stat-label">Active Mappings</div>
                    </div>
                    <div class="stat">
                        <div class="stat-value">
                            <?php 
                            $totalMatched = array_sum(array_column($existingMappings, 'times_matched'));
                            echo $totalMatched;
                            ?>
                        </div>
                        <div class="stat-label">Auto-Matched</div>
                    </div>
                </div>
                
                <div class="alert alert-info mt-3">
                    <strong>💡 How it works:</strong> When you create a product mapping, future Etsy orders 
                    containing that product will automatically link to the selected project. This makes creating 
                    estimates faster and provides better sales analytics.
                </div>
            </div>
        </div>

        <!-- Unlinked Products -->
        <div class="card mb-4">
            <div class="card-header">
                <h2 class="card-title">Unlinked Products</h2>
                <p class="card-subtitle">Products sold on Etsy that aren't yet linked to OMC projects</p>
            </div>
            <div class="card-body">
                <?php if (empty($unlinkedProducts)): ?>
                    <div class="alert alert-success">
                        <strong>🎉 All products are linked!</strong><br>
                        All your Etsy products are already linked to OMC projects.
                    </div>
                <?php else: ?>
                    <?php foreach ($unlinkedProducts as $product): ?>
                        <div class="product-card">
                            <div style="display: flex; justify-content: space-between; align-items: start;">
                                <div style="flex: 1;">
                                    <h3 style="margin: 0 0 0.5rem 0; font-size: 1.125rem;">
                                        <?php echo htmlspecialchars($product['product_name']); ?>
                                    </h3>
                                    <?php if ($product['product_sku']): ?>
                                        <p style="margin: 0; color: #6b7280; font-size: 0.875rem;">
                                            SKU: <?php echo htmlspecialchars($product['product_sku']); ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                                <div style="text-align: right;">
                                    <div style="font-size: 0.875rem; color: #6b7280;">
                                        Sold <?php echo $product['times_ordered']; ?> time<?php echo $product['times_ordered'] > 1 ? 's' : ''; ?>
                                    </div>
                                    <div style="font-size: 0.875rem; color: #6b7280;">
                                        Total: $<?php echo number_format($product['total_revenue'], 2); ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Linking Form -->
                            <div class="mapping-form">
                                <form method="POST">
                                    <input type="hidden" name="action" value="create_mapping">
                                    <input type="hidden" name="product_name" value="<?php echo htmlspecialchars($product['product_name']); ?>">
                                    <?php if ($product['product_sku']): ?>
                                        <input type="hidden" name="product_sku" value="<?php echo htmlspecialchars($product['product_sku']); ?>">
                                    <?php endif; ?>
                                    
                                    <div class="form-row">
                                        <div class="form-group" style="flex: 2;">
                                            <label for="project_<?php echo md5($product['product_name']); ?>" class="form-label">
                                                Link to Project *
                                            </label>
                                            <select name="project_id" id="project_<?php echo md5($product['product_name']); ?>" 
                                                    class="form-control" required>
                                                <option value="">-- Select Project --</option>
                                                <?php foreach ($projects as $project): ?>
                                                    <option value="<?php echo $project['id']; ?>">
                                                        <?php echo htmlspecialchars($project['project_name']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="match_type_<?php echo md5($product['product_name']); ?>" class="form-label">
                                                Match By *
                                            </label>
                                            <select name="match_type" id="match_type_<?php echo md5($product['product_name']); ?>" 
                                                    class="form-control" required>
                                                <option value="name">Product Name</option>
                                                <?php if ($product['product_sku']): ?>
                                                    <option value="sku" selected>SKU</option>
                                                <?php endif; ?>
                                            </select>
                                        </div>
                                        
                                        <div class="form-group" style="display: flex; align-items: flex-end;">
                                            <button type="submit" class="btn btn-primary">
                                                <span class="icon">🔗</span> Create Mapping
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Existing Mappings -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Active Product Mappings</h2>
                <p class="card-subtitle">Currently active product-to-project links</p>
            </div>
            <div class="card-body">
                <?php if (empty($existingMappings)): ?>
                    <p style="color: #6b7280;">No product mappings created yet.</p>
                <?php else: ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Match Type</th>
                                <th>Linked Project</th>
                                <th>Times Matched</th>
                                <th>Last Used</th>
                                <th>Created</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($existingMappings as $mapping): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($mapping['product_name'] ?? 'SKU: ' . $mapping['product_sku']); ?></strong>
                                        <?php if ($mapping['product_sku']): ?>
                                            <br><small style="color: #6b7280;">SKU: <?php echo htmlspecialchars($mapping['product_sku']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-info">
                                            <?php echo htmlspecialchars($mapping['match_type']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($mapping['project_name']); ?></td>
                                    <td><?php echo $mapping['times_matched']; ?></td>
                                    <td>
                                        <?php echo $mapping['last_matched_at'] ? date('M j, Y', strtotime($mapping['last_matched_at'])) : 'Never'; ?>
                                    </td>
                                    <td><?php echo date('M j, Y', strtotime($mapping['created_at'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </main>
</body>
</html>
