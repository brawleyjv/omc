<?php
require_once realpath(dirname(__FILE__) . '/../../config.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estimates - OMC</title>
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
                    <h1>Estimates</h1>
                    <p>Create and manage project estimates</p>
                </div>
            </div>
            <nav class="header-nav">
                <a href="<?php echo BASE_URL; ?>Views/main.php" class="nav-link">Dashboard</a>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-container">
        <div class="page-header">
            <div class="page-header-content">
                <h1 class="page-title">Estimate Management</h1>
            </div>
        </div>

        <div class="menu-grid">
            <a href="<?php echo BASE_URL; ?>Views/estimate/create_new_estimate.php" class="menu-card">
                <div class="menu-card-icon">
                    <span class="icon">➕</span>
                </div>
                <h3 class="menu-card-title">Create New Estimate</h3>
                <p class="menu-card-description">Build a new estimate from scratch with materials, labor, and pricing</p>
            </a>

            <a href="<?php echo BASE_URL; ?>Views/estimate/list_estimates.php" class="menu-card">
                <div class="menu-card-icon">
                    <span class="icon">📋</span>
                </div>
                <h3 class="menu-card-title">View All Estimates</h3>
                <p class="menu-card-description">Browse and manage existing estimates and quotes</p>
            </a>
        </div>
    </main>
</body>
</html>
