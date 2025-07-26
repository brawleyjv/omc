<?php
require_once __DIR__ . '/../../config.php'; // Load config first to get BASE_PATH
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Board Feet Calculator - OMC</title>
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
                    <h1>Board Feet Calculator</h1>
                    <p>Calculate material board feet requirements</p>
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
                <h1 class="page-title">Board Feet Calculator</h1>
                <div class="page-actions">
                    <a href="<?php echo BASE_URL; ?>Views/main.php" class="btn btn-secondary">
                        <span class="icon">🏠</span>
                        Main Menu
                    </a>
                </div>
            </div>
        </div>

        <!-- Calculator Form -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Calculate Board Feet</h2>
                <p class="card-subtitle">Enter material dimensions to calculate board feet</p>
            </div>
            <div class="card-body">
                <form method="post" action="">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="form-group">
                            <label for="length" class="form-label required">Length (inches)</label>
                            <input type="number" id="length" name="length" class="form-control" 
                                   value="<?php echo isset($_POST['length']) ? htmlspecialchars($_POST['length']) : ''; ?>"
                                   placeholder="Enter length..." step="0.01" required>
                            <small class="form-text">Material length in inches</small>
                        </div>

                        <div class="form-group">
                            <label for="width" class="form-label required">Width (inches)</label>
                            <input type="number" id="width" name="width" class="form-control" 
                                   value="<?php echo isset($_POST['width']) ? htmlspecialchars($_POST['width']) : ''; ?>"
                                   placeholder="Enter width..." step="0.01" required>
                            <small class="form-text">Material width in inches</small>
                        </div>

                        <div class="form-group">
                            <label for="thickness" class="form-label required">Thickness (inches)</label>
                            <input type="number" id="thickness" name="thickness" class="form-control" 
                                   value="<?php echo isset($_POST['thickness']) ? htmlspecialchars($_POST['thickness']) : ''; ?>"
                                   placeholder="Enter thickness..." step="0.01" required>
                            <small class="form-text">Material thickness in inches</small>
                        </div>
                    </div>

                    <div class="form-actions mt-6">
                        <button type="submit" class="btn btn-primary">
                            <span class="icon">🧮</span>
                            Calculate Board Feet
                        </button>
                        <button type="reset" class="btn btn-secondary">
                            <span class="icon">🔄</span>
                            Reset
                        </button>
                    </div>
                </form>

                <!-- Results Display -->
                <?php
                if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['length']) && !empty($_POST['width']) && !empty($_POST['thickness'])) {
                    $length = floatval($_POST['length']);
                    $width = floatval($_POST['width']);
                    $thickness = floatval($_POST['thickness']);
                    $boardFeet = ($length * $width * $thickness) / 144;
                ?>
                <div class="notification notification-success mt-6">
                    <div class="notification-content">
                        <h3 class="notification-title">Calculation Result</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                            <div class="stat-card">
                                <div class="stat-label">Dimensions</div>
                                <div class="stat-value"><?php echo number_format($length, 2); ?>" × <?php echo number_format($width, 2); ?>" × <?php echo number_format($thickness, 2); ?>"</div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-label">Board Feet</div>
                                <div class="stat-value text-primary"><?php echo number_format($boardFeet, 4); ?> BF</div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php } ?>
            </div>
        </div>

        <!-- Information Card -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Board Feet Formula</h3>
            </div>
            <div class="card-body">
                <p class="text-muted">Board Feet = (Length × Width × Thickness) ÷ 144</p>
                <p class="text-muted">All measurements must be in inches. The result represents the volume of lumber in board feet.</p>
            </div>
        </div>
    </main>
</body>
</html>
