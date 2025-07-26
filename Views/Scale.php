<?php
require_once __DIR__ . '/../config.php'; // Load config first to get BASE_PATH
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scale Project Calculator - OMC</title>
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
                    <h1>Scale Project Calculator</h1>
                    <p>Calculate scaling percentages for material thickness</p>
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
                <h1 class="page-title">Scale Project Calculator</h1>
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
                <h2 class="card-title">Material Scaling Calculator</h2>
                <p class="card-subtitle">Calculate the scale percentage needed based on material and drawing thickness</p>
            </div>
            <div class="card-body">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Material Thickness -->
                    <div class="form-group">
                        <label for="material-thickness" class="form-label required">Material Thickness</label>
                        <div class="form-input-group">
                            <input type="number" step="0.001" id="material-thickness" name="material-thickness" 
                                   class="form-control" placeholder="Enter thickness..." required>
                            <select id="material-unit" name="material-unit" class="form-control" style="max-width: 120px;">
                                <option value="imperial">Inches</option>
                                <option value="metric">Millimeters</option>
                            </select>
                        </div>
                        <small class="form-text">Thickness of the material you are using</small>
                    </div>

                    <!-- Drawing Thickness -->
                    <div class="form-group">
                        <label for="drawing-thickness" class="form-label required">Project Drawing Thickness</label>
                        <div class="form-input-group">
                            <input type="number" step="0.001" id="drawing-thickness" name="drawing-thickness" 
                                   class="form-control" placeholder="Enter thickness..." required>
                            <select id="drawing-unit" name="drawing-unit" class="form-control" style="max-width: 120px;">
                                <option value="imperial">Inches</option>
                                <option value="metric">Millimeters</option>
                            </select>
                        </div>
                        <small class="form-text">Thickness specified in the project drawing</small>
                    </div>
                </div>

                <div class="form-actions mt-6">
                    <button type="button" class="btn btn-primary" onclick="calculateScale()">
                        <span class="icon">📏</span>
                        Calculate Scale
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="clearFields()">
                        <span class="icon">🔄</span>
                        Clear Fields
                    </button>
                </div>

                <!-- Results Display -->
                <div id="result-container" style="display: none;" class="mt-6">
                    <div class="notification notification-info">
                        <div class="notification-content">
                            <h3 class="notification-title">Scaling Result</h3>
                            <div id="result" class="mt-2"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Information Card -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">How It Works</h3>
            </div>
            <div class="card-body">
                <p class="text-muted mb-4">This calculator determines the scaling percentage needed when your material thickness differs from the project drawing specifications.</p>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="info-item">
                        <h4 class="font-medium">Material Thickness</h4>
                        <p class="text-muted">The actual thickness of the material you plan to use for the project.</p>
                    </div>
                    <div class="info-item">
                        <h4 class="font-medium">Drawing Thickness</h4>
                        <p class="text-muted">The thickness specified in the original project design or drawing.</p>
                    </div>
                </div>

                <div class="mt-4">
                    <h4 class="font-medium">Scaling Formula</h4>
                    <p class="text-muted">Scale Percentage = (Material Thickness ÷ Drawing Thickness) × 100</p>
                </div>
            </div>
        </div>
    </main>

    <script>
        function convertToImperial(value, unit) {
            if (unit === 'metric') {
                return value * 0.0393701; // Convert millimeters to inches
            }
            return value;
        }

        function calculateScale() {
            const materialThickness = parseFloat(document.getElementById('material-thickness').value);
            const materialUnit = document.getElementById('material-unit').value;
            const drawingThickness = parseFloat(document.getElementById('drawing-thickness').value);
            const drawingUnit = document.getElementById('drawing-unit').value;

            const resultContainer = document.getElementById('result-container');
            const resultElement = document.getElementById('result');

            if (!materialThickness || !drawingThickness) {
                resultElement.innerHTML = '<span class="text-danger">Please enter valid numbers for both thicknesses.</span>';
                resultContainer.style.display = 'block';
                return;
            }

            const materialThicknessImperial = convertToImperial(materialThickness, materialUnit);
            const drawingThicknessImperial = convertToImperial(drawingThickness, drawingUnit);

            if (Math.abs(materialThicknessImperial - drawingThicknessImperial) < 0.0001) {
                resultElement.innerHTML = '<span class="text-success">✅ No scaling is required - thicknesses match!</span>';
            } else {
                const scalePercentage = (materialThicknessImperial / drawingThicknessImperial) * 100;
                const difference = Math.abs(100 - scalePercentage);
                
                if (scalePercentage > 100) {
                    resultElement.innerHTML = `
                        <div class="mb-2"><span class="text-primary">📈 Scale UP by ${scalePercentage.toFixed(2)}%</span></div>
                        <div class="text-muted">Your material is ${difference.toFixed(2)}% thicker than the drawing specification.</div>
                    `;
                } else {
                    resultElement.innerHTML = `
                        <div class="mb-2"><span class="text-warning">📉 Scale DOWN by ${scalePercentage.toFixed(2)}%</span></div>
                        <div class="text-muted">Your material is ${difference.toFixed(2)}% thinner than the drawing specification.</div>
                    `;
                }
            }

            resultContainer.style.display = 'block';
        }

        function clearFields() {
            document.getElementById('material-thickness').value = '';
            document.getElementById('material-unit').value = 'imperial';
            document.getElementById('drawing-thickness').value = '';
            document.getElementById('drawing-unit').value = 'imperial';
            document.getElementById('result-container').style.display = 'none';
        }

        // Allow Enter key to calculate
        document.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                calculateScale();
            }
        });
    </script>
</body>
</html>
