<?php
require_once __DIR__ . '/../../config.php'; // Load config first to get BASE_PATH
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rotary Setup Calculator - OMC</title>
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
                    <h1>Rotary Setup Calculator</h1>
                    <p>Calculate steps per rotation for rotary axis setup</p>
                </div>
            </div>
            <nav class="header-nav">
                <a href="<?php echo BASE_URL; ?>Views/main.php" class="nav-link">Dashboard</a>
                <a href="<?php echo BASE_URL; ?>Views/rotary/index.php" class="nav-link">Rotary Management</a>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-container">
        <!-- Page Header -->
        <div class="page-header">
            <div class="page-header-content">
                <h1 class="page-title">Rotary Steps Calculator</h1>
                <div class="page-actions">
                    <a href="<?php echo BASE_URL; ?>Views/rotary/index.php" class="btn btn-secondary">
                        <span class="icon">🔧</span>
                        Rotary Management
                    </a>
                </div>
            </div>
        </div>

        <!-- Calculator Form -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Calculate Steps Per Rotation</h2>
                <p class="card-subtitle">Enter your motor and mechanical specifications</p>
            </div>
            <div class="card-body">
                <form method="post">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="form-group">
                            <label for="motorSteps" class="form-label required">Motor Steps Per Revolution</label>
                            <input type="number" id="motorSteps" name="motorSteps" class="form-control" 
                                   value="<?php echo isset($_POST['motorSteps']) ? htmlspecialchars($_POST['motorSteps']) : '200'; ?>"
                                   placeholder="Enter motor steps..." required>
                            <small class="form-text">Typically 200 for standard stepper motors</small>
                        </div>

                        <div class="form-group">
                            <label for="microSteps" class="form-label required">Microstepping Factor</label>
                            <select id="microSteps" name="microSteps" class="form-control">
                                <option value="1" <?php if (isset($_POST['microSteps']) && $_POST['microSteps'] == '1') echo 'selected'; ?>>1 (Full Step)</option>
                                <option value="2" <?php if (isset($_POST['microSteps']) && $_POST['microSteps'] == '2') echo 'selected'; ?>>2 (Half Step)</option>
                                <option value="4" <?php if (isset($_POST['microSteps']) && $_POST['microSteps'] == '4') echo 'selected'; ?>>4</option>
                                <option value="8" <?php if (isset($_POST['microSteps']) && $_POST['microSteps'] == '8') echo 'selected'; ?>>8</option>
                                <option value="16" <?php if (!isset($_POST['microSteps']) || $_POST['microSteps'] == '16') echo 'selected'; ?>>16</option>
                                <option value="32" <?php if (isset($_POST['microSteps']) && $_POST['microSteps'] == '32') echo 'selected'; ?>>32</option>
                            </select>
                            <small class="form-text">Microstepping configuration of your driver</small>
                        </div>

                        <div class="form-group">
                            <label for="rollerDiameter" class="form-label required">Roller Diameter (inches)</label>
                            <input type="number" step="0.001" id="rollerDiameter" name="rollerDiameter" class="form-control" 
                                   value="<?php echo isset($_POST['rollerDiameter']) ? htmlspecialchars($_POST['rollerDiameter']) : '2.0'; ?>"
                                   placeholder="Enter roller diameter..." required>
                            <small class="form-text">Diameter of the drive roller or pulley</small>
                        </div>

                        <div class="form-group">
                            <label for="workpieceDiameter" class="form-label required">Workpiece Diameter (inches)</label>
                            <input type="number" step="0.001" id="workpieceDiameter" name="workpieceDiameter" class="form-control" 
                                   value="<?php echo isset($_POST['workpieceDiameter']) ? htmlspecialchars($_POST['workpieceDiameter']) : '3.5'; ?>"
                                   placeholder="Enter workpiece diameter..." required>
                            <small class="form-text">Diameter of the workpiece being rotated</small>
                        </div>
                    </div>

                    <div class="form-actions mt-6">
                        <button type="submit" class="btn btn-primary">
                            <span class="icon">⚙️</span>
                            Calculate Steps
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="resetForm()">
                            <span class="icon">🔄</span>
                            Reset
                        </button>
                    </div>
                </form>

                <!-- Results Display -->
                <?php
                if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['motorSteps']) && !empty($_POST['microSteps']) && !empty($_POST['rollerDiameter']) && !empty($_POST['workpieceDiameter'])) {
                    function calculateStepsPerRotation($motorSteps, $microSteps, $rollerDiameter, $workpieceDiameter) {
                        // Convert diameters to circumferences
                        $rollerCircumference = pi() * $rollerDiameter;
                        $workpieceCircumference = pi() * $workpieceDiameter;

                        // Calculate steps per rotation
                        $stepsPerRotation = ($motorSteps * $microSteps) * ($workpieceCircumference / $rollerCircumference);

                        return round($stepsPerRotation, 2);
                    }

                    // Get user input
                    $motorSteps = floatval($_POST["motorSteps"]);
                    $microSteps = floatval($_POST["microSteps"]);
                    $rollerDiameter = floatval($_POST["rollerDiameter"]);
                    $workpieceDiameter = floatval($_POST["workpieceDiameter"]);

                    // Calculate result
                    $calculatedSteps = calculateStepsPerRotation($motorSteps, $microSteps, $rollerDiameter, $workpieceDiameter);
                    
                    // Calculate ratios and additional info
                    $ratio = $workpieceDiameter / $rollerDiameter;
                    $degreesPerStep = 360 / $calculatedSteps;
                ?>
                <div class="notification notification-success mt-6">
                    <div class="notification-content">
                        <h3 class="notification-title">Calculation Results</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                            <div class="stat-card">
                                <div class="stat-label">Steps per Rotation</div>
                                <div class="stat-value text-primary"><?php echo number_format($calculatedSteps, 2); ?></div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-label">Degrees per Step</div>
                                <div class="stat-value"><?php echo number_format($degreesPerStep, 4); ?>°</div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-label">Diameter Ratio</div>
                                <div class="stat-value"><?php echo number_format($ratio, 3); ?>:1</div>
                            </div>
                        </div>
                        <div class="mt-4 text-muted">
                            <p><strong>Configuration:</strong> <?php echo $motorSteps; ?> steps/rev × <?php echo $microSteps; ?> microsteps × <?php echo number_format($ratio, 3); ?> ratio</p>
                        </div>
                    </div>
                </div>
                <?php } ?>
            </div>
        </div>

        <!-- Information Card -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Rotary Setup Information</h3>
            </div>
            <div class="card-body">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h4 class="font-medium mb-2">Formula</h4>
                        <p class="text-muted">Steps per Rotation = (Motor Steps × Microsteps) × (Workpiece Circumference ÷ Roller Circumference)</p>
                    </div>
                    <div>
                        <h4 class="font-medium mb-2">Common Values</h4>
                        <ul class="text-muted space-y-1">
                            <li><strong>Motor Steps:</strong> 200 (1.8° per step) or 400 (0.9° per step)</li>
                            <li><strong>Microsteps:</strong> 8, 16, or 32 (common settings)</li>
                            <li><strong>Accuracy:</strong> Higher microsteps = smoother motion</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        function resetForm() {
            document.getElementById('motorSteps').value = '200';
            document.getElementById('microSteps').value = '16';
            document.getElementById('rollerDiameter').value = '2.0';
            document.getElementById('workpieceDiameter').value = '3.5';
        }
    </script>
</body>
</html>
