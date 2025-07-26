<?php
require_once __DIR__ . '/../../config.php'; // Load config first to get BASE_PATH
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chipload Calculator - OMC</title>
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
                    <h1>Chipload Calculator</h1>
                    <p>Calculate optimal feed rates and RPM for CNC machining</p>
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
                <h1 class="page-title">Chipload Calculator</h1>
                <div class="page-actions">
                    <a href="<?php echo BASE_URL; ?>Views/main.php" class="btn btn-secondary">
                        <span class="icon">🏠</span>
                        Main Menu
                    </a>
                </div>
            </div>
        </div>

        <!-- Material Guidelines -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Material Speed Guidelines</h2>
                <p class="card-subtitle">Recommended feed rates for common materials</p>
            </div>
            <div class="card-body">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="guideline-card">
                        <h3 class="text-primary font-medium mb-2">🌲 Wood</h3>
                        <ul class="text-muted space-y-1">
                            <li><strong>Softwoods:</strong> 100–300 IPM</li>
                            <li><strong>Hardwoods:</strong> 75–200 IPM</li>
                            <li><strong>Plywood/MDF:</strong> 100–250 IPM</li>
                        </ul>
                    </div>
                    <div class="guideline-card">
                        <h3 class="text-primary font-medium mb-2">🔮 Acrylic</h3>
                        <ul class="text-muted space-y-1">
                            <li><strong>Small bits (1/8"):</strong> 75–100 IPM</li>
                            <li><strong>Large bits (1/2"):</strong> 200–300 IPM</li>
                            <li><strong>Note:</strong> Ensure proper cooling</li>
                        </ul>
                    </div>
                    <div class="guideline-card">
                        <h3 class="text-primary font-medium mb-2">🔧 Aluminum</h3>
                        <ul class="text-muted space-y-1">
                            <li><strong>Small bits:</strong> 10–50 IPM</li>
                            <li><strong>Large bits:</strong> 50–150 IPM</li>
                            <li><strong>Note:</strong> Depends on grade and tool type</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Calculator Form -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Calculate Feed Rate and RPM</h2>
                <p class="card-subtitle">Enter your machining parameters to get optimal settings</p>
            </div>
            <div class="card-body">
                <form id="chipload-form">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="form-group">
                            <label for="material" class="form-label required">Material</label>
                            <select id="material" name="material" class="form-control" required>
                                <option value="">Select material...</option>
                                <option value="wood">Wood</option>
                                <option value="acrylic">Acrylic</option>
                                <option value="aluminum">Aluminum</option>
                            </select>
                            <small class="form-text">Material you are machining</small>
                        </div>

                        <div class="form-group">
                            <label for="bit_size" class="form-label required">Bit Size</label>
                            <select id="bit_size" name="bit_size" class="form-control" required>
                                <option value="">Select bit size...</option>
                                <option value="0.125">1/8" (0.125")</option>
                                <option value="0.25">1/4" (0.25")</option>
                                <option value="0.375">3/8" (0.375")</option>
                                <option value="0.5">1/2" (0.5")</option>
                            </select>
                            <small class="form-text">Diameter of the cutting bit</small>
                        </div>

                        <div class="form-group">
                            <label for="flute_count" class="form-label required">Flute Count</label>
                            <input type="number" id="flute_count" name="flute_count" class="form-control" 
                                   placeholder="Enter flute count..." min="1" max="8" required>
                            <small class="form-text">Number of cutting edges on the bit</small>
                        </div>

                        <div class="form-group">
                            <label for="max_rpm" class="form-label required">Maximum RPM</label>
                            <input type="number" id="max_rpm" name="max_rpm" class="form-control" 
                                   placeholder="Enter max RPM..." min="1000" max="50000" required>
                            <small class="form-text">Maximum spindle speed available</small>
                        </div>
                    </div>

                    <div class="form-actions mt-6">
                        <button type="button" class="btn btn-primary" onclick="calculateFeedRate()">
                            <span class="icon">⚙️</span>
                            Calculate Settings
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="clearForm()">
                            <span class="icon">🔄</span>
                            Clear Form
                        </button>
                    </div>
                </form>

                <!-- Results Display -->
                <div id="result-container" style="display: none;" class="mt-6">
                    <div class="notification notification-success">
                        <div class="notification-content">
                            <h3 class="notification-title">Recommended Settings</h3>
                            <div id="result" class="mt-4"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Information Card -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Chipload Formula</h3>
            </div>
            <div class="card-body">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h4 class="font-medium mb-2">Feed Rate Calculation</h4>
                        <p class="text-muted">Feed Rate (IPM) = Flute Count × Chipload × RPM</p>
                    </div>
                    <div>
                        <h4 class="font-medium mb-2">Chipload Values</h4>
                        <p class="text-muted">Optimal chip size per tooth for different materials and bit sizes</p>
                    </div>
                </div>
                <div class="mt-4">
                    <p class="text-muted"><strong>Important:</strong> Start with conservative settings and adjust based on material behavior and cut quality. Always monitor for excessive heat buildup or chatter.</p>
                </div>
            </div>
        </div>
    </main>

    <script>
        function calculateFeedRate() {
            const material = document.getElementById('material').value;
            const bitSize = parseFloat(document.getElementById('bit_size').value);
            const fluteCount = parseInt(document.getElementById('flute_count').value);
            const maxRpm = parseInt(document.getElementById('max_rpm').value);

            const resultContainer = document.getElementById('result-container');
            const resultElement = document.getElementById('result');

            if (!material || !bitSize || !fluteCount || !maxRpm) {
                resultElement.innerHTML = '<span class="text-danger">Please fill in all fields.</span>';
                resultContainer.style.display = 'block';
                return;
            }

            let chipload;

            // Chipload values based on material and bit size
            if (material === 'wood') {
                if (bitSize <= 0.125) chipload = 0.004;
                else if (bitSize <= 0.25) chipload = 0.0075;
                else if (bitSize <= 0.375) chipload = 0.01;
                else chipload = 0.015;
            } else if (material === 'acrylic') {
                if (bitSize <= 0.125) chipload = 0.003;
                else if (bitSize <= 0.25) chipload = 0.006;
                else if (bitSize <= 0.375) chipload = 0.008;
                else chipload = 0.01;
            } else if (material === 'aluminum') {
                if (bitSize <= 0.125) chipload = 0.0015;
                else if (bitSize <= 0.25) chipload = 0.003;
                else if (bitSize <= 0.375) chipload = 0.004;
                else chipload = 0.005;
            }

            const feedRate = (fluteCount * chipload * maxRpm).toFixed(2);
            const actualChipload = (parseFloat(feedRate) / (fluteCount * maxRpm)).toFixed(4);

            resultElement.innerHTML = `
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="stat-card">
                        <div class="stat-label">Recommended RPM</div>
                        <div class="stat-value text-primary">${maxRpm.toLocaleString()}</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-label">Feed Rate</div>
                        <div class="stat-value text-success">${feedRate} IPM</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-label">Chipload</div>
                        <div class="stat-value">${actualChipload}"</div>
                    </div>
                </div>
                <div class="mt-4 text-muted">
                    <p><strong>Material:</strong> ${material.charAt(0).toUpperCase() + material.slice(1)} | 
                       <strong>Bit Size:</strong> ${bitSize}" | 
                       <strong>Flutes:</strong> ${fluteCount}</p>
                </div>
            `;

            resultContainer.style.display = 'block';
        }

        function clearForm() {
            document.getElementById('chipload-form').reset();
            document.getElementById('result-container').style.display = 'none';
        }

        // Allow Enter key to calculate
        document.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                calculateFeedRate();
            }
        });
    </script>
</body>
</html>
