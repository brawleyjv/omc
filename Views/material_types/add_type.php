<?php
require_once realpath(dirname(__FILE__) . '/../../config.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Material Type - OMC</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>styles-modern.css">
</head>
<body>
    <!-- Modern Header -->
    <header class="header">
        <div class="header-content">
            <div class="header-brand">
                <div class="header-brand-text">
                    <h1>Add Material Type</h1>
                    <p>Create a new material type classification</p>
                </div>
            </div>
            <nav class="header-nav">
                <a href="<?php echo BASE_URL; ?>Views/main.php" class="nav-link">Dashboard</a>
                <a href="<?php echo BASE_URL; ?>Views/materials/list_materials.php" class="nav-link">Materials</a>
                <a href="<?php echo BASE_URL; ?>Views/material_types/list_types.php" class="nav-link">Material Types</a>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-container">
        <!-- Page Header -->
        <div class="page-header">
            <div class="page-header-content">
                <h1 class="page-title">Create Material Type</h1>
                <div class="page-actions">
                    <a href="<?php echo BASE_URL; ?>Views/material_types/list_types.php" class="btn btn-ghost">
                        <span class="icon">📝</span>
                        All Types
                    </a>
                </div>
            </div>
        </div>

        <!-- Add Type Form -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Material Type Details</h2>
                <p class="card-subtitle">Enter the information for the new material type</p>
            </div>
            <div class="card-body">
                <form action="<?php echo BASE_URL; ?>public/material_types/add_type.php" method="post">
                    <div class="form-section">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="type_name" class="form-label">Type Name *</label>
                                <input type="text" id="type_name" name="type_name" class="form-control" required 
                                       placeholder="Enter material type name (e.g., Wood, Metal, Plastic)">
                                <small class="form-text">Provide a descriptive name for this material type</small>
                            </div>
                        </div>

                        <div class="notification notification-info">
                            <strong>Note:</strong> Material types help organize and categorize your materials for easier management and filtering.
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <span class="icon">📝</span>
                            Create Type
                        </button>
                        <a href="<?php echo BASE_URL; ?>Views/material_types/list_types.php" class="btn btn-secondary">
                            <span class="icon">✖️</span>
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </main>
</body>
</html>
