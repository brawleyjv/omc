<?php
require_once __DIR__ . '/../../config.php'; // Load config first to get BASE_PATH
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rotary Management - OMC</title>
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
                    <h1>Rotary Management</h1>
                    <p>CNC rotary axis tools and calculators</p>
                </div>
            </div>
            <nav class="header-nav">
                <a href="<?php echo BASE_URL; ?>Views/main.php" class="nav-link">Dashboard</a>
                <a href="<?php echo BASE_URL; ?>Views/projects/index.php" class="nav-link">Projects</a>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-container">
        <!-- Page Header -->
        <div class="page-header">
            <div class="page-header-content">
                <h1 class="page-title">Rotary Management</h1>
                <div class="page-actions">
                    <a href="<?php echo BASE_URL; ?>Views/main.php" class="btn btn-secondary">
                        <span class="icon">🏠</span>
                        Main Menu
                    </a>
                </div>
            </div>
        </div>

        <!-- Rotary Tools Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Rotary Setup Calculator -->
            <div class="card hover-card">
                <div class="card-body text-center">
                    <div class="icon-wrapper mb-4">
                        <span class="icon text-6xl">⚙️</span>
                    </div>
                    <h3 class="card-title">Rotary Setup Calculator</h3>
                    <p class="text-muted mb-6">Calculate steps per rotation for your rotary axis configuration</p>
                    <a href="<?php echo BASE_URL; ?>Views/rotary/rotary_setup.php" class="btn btn-primary">
                        <span class="icon">🧮</span>
                        Open Calculator
                    </a>
                </div>
            </div>

            <!-- Future Tools Placeholder -->
            <div class="card hover-card opacity-60">
                <div class="card-body text-center">
                    <div class="icon-wrapper mb-4">
                        <span class="icon text-6xl">🔧</span>
                    </div>
                    <h3 class="card-title">Rotary Calibration</h3>
                    <p class="text-muted mb-6">Calibration tools for rotary axis accuracy (Coming Soon)</p>
                    <button class="btn btn-secondary" disabled>
                        <span class="icon">🚧</span>
                        Coming Soon
                    </button>
                </div>
            </div>

            <div class="card hover-card opacity-60">
                <div class="card-body text-center">
                    <div class="icon-wrapper mb-4">
                        <span class="icon text-6xl">📐</span>
                    </div>
                    <h3 class="card-title">Angle Calculator</h3>
                    <p class="text-muted mb-6">Calculate angles and positions for rotary operations (Coming Soon)</p>
                    <button class="btn btn-secondary" disabled>
                        <span class="icon">🚧</span>
                        Coming Soon
                    </button>
                </div>
            </div>
        </div>

        <!-- Information Card -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">About Rotary Management</h3>
            </div>
            <div class="card-body">
                <p class="text-muted mb-4">The Rotary Management section provides tools and calculators for setting up and operating CNC rotary axes. These tools help ensure accurate rotation, proper scaling, and optimal performance for cylindrical machining operations.</p>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="info-section">
                        <h4 class="font-medium mb-2">Current Features</h4>
                        <ul class="text-muted space-y-1">
                            <li>• Steps per rotation calculator</li>
                            <li>• Motor and microstep configuration</li>
                            <li>• Diameter ratio calculations</li>
                            <li>• Precision setup guidance</li>
                        </ul>
                    </div>
                    <div class="info-section">
                        <h4 class="font-medium mb-2">Planned Features</h4>
                        <ul class="text-muted space-y-1">
                            <li>• Calibration utilities</li>
                            <li>• Angle positioning tools</li>
                            <li>• Multi-axis coordination</li>
                            <li>• Setup validation tests</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
