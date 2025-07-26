<?php
require_once realpath(dirname(__FILE__) . '/config.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OMC Design System Demo</title>
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
                    <h1>OMC Design System</h1>
                    <p>Modern Professional Interface Demo</p>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-container">
        <!-- Welcome Section -->
        <div class="card card-primary mb-4">
            <div class="card-header">
                <h2 class="card-title">🎨 New Modern Design System</h2>
                <p class="card-subtitle">Professional, card-based interface with improved colors and user experience</p>
            </div>
        </div>

        <!-- Design Comparison -->
        <div class="card-grid">
            <!-- Old Design -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">❌ Old Design</h3>
                </div>
                <div class="card-body">
                    <ul style="color: var(--text-secondary); line-height: 1.8;">
                        <li>Basic button styling</li>
                        <li>Limited color palette</li>
                        <li>Simple layout structure</li>
                        <li>Basic typography</li>
                        <li>Minimal visual hierarchy</li>
                    </ul>
                    <div class="mt-3">
                        <a href="<?php echo BASE_URL; ?>Views/main.php" class="btn btn-outline btn-sm">View Old Main Page</a>
                    </div>
                </div>
            </div>

            <!-- New Design -->
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title">✅ New Modern Design</h3>
                </div>
                <div class="card-body">
                    <ul style="color: var(--text-secondary); line-height: 1.8;">
                        <li><strong>Card-based interface</strong></li>
                        <li><strong>Professional color system</strong></li>
                        <li><strong>Modern typography (Inter font)</strong></li>
                        <li><strong>Hover effects & animations</strong></li>
                        <li><strong>Responsive grid layouts</strong></li>
                        <li><strong>Improved accessibility</strong></li>
                    </ul>
                    <div class="mt-3">
                        <a href="<?php echo BASE_URL; ?>Views/main-modern.php" class="btn btn-primary btn-sm">View New Main Page</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Live Demos -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">🚀 Live Demos</h3>
                <p class="card-subtitle">Compare the old and new designs side by side</p>
            </div>
            <div class="card-body">
                <div class="menu-grid">
                    <!-- Main Dashboard -->
                    <div class="menu-card" onclick="window.open('<?php echo BASE_URL; ?>Views/main-modern.php', '_blank')">
                        <div class="menu-card-icon">
                            <span class="icon">🏠</span>
                        </div>
                        <h4 class="menu-card-title">Modern Dashboard</h4>
                        <p class="menu-card-description">New card-based main navigation with improved UX</p>
                    </div>

                    <!-- Vendor List -->
                    <div class="menu-card" onclick="window.open('<?php echo BASE_URL; ?>Views/vendors/list_vendors-modern.php', '_blank')">
                        <div class="menu-card-icon">
                            <span class="icon">🏪</span>
                        </div>
                        <h4 class="menu-card-title">Modern Vendor List</h4>
                        <p class="menu-card-description">Professional table design with modern styling</p>
                    </div>

                    <!-- Component Library -->
                    <div class="menu-card" onclick="scrollToComponents()">
                        <div class="menu-card-icon">
                            <span class="icon">🎨</span>
                        </div>
                        <h4 class="menu-card-title">Component Library</h4>
                        <p class="menu-card-description">See all available UI components and styles</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Component Library -->
        <div id="components" class="card">
            <div class="card-header">
                <h3 class="card-title">🎨 Component Library</h3>
                <p class="card-subtitle">All available UI components in the new design system</p>
            </div>
            <div class="card-body">
                
                <!-- Buttons -->
                <h4 class="mb-3">Buttons</h4>
                <div style="display: flex; gap: 1rem; flex-wrap: wrap; margin-bottom: 2rem;">
                    <button class="btn btn-primary">Primary Button</button>
                    <button class="btn btn-secondary">Secondary Button</button>
                    <button class="btn btn-outline">Outline Button</button>
                    <button class="btn btn-ghost">Ghost Button</button>
                    <button class="btn btn-danger">Danger Button</button>
                </div>

                <!-- Button Sizes -->
                <h4 class="mb-3">Button Sizes</h4>
                <div style="display: flex; gap: 1rem; flex-wrap: wrap; margin-bottom: 2rem; align-items: center;">
                    <button class="btn btn-primary btn-sm">Small</button>
                    <button class="btn btn-primary">Regular</button>
                    <button class="btn btn-primary btn-lg">Large</button>
                </div>

                <!-- Cards -->
                <h4 class="mb-3">Card Variants</h4>
                <div class="card-grid">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h5 class="card-title">Primary Card</h5>
                        </div>
                        <div class="card-body">
                            <p>Used for main content areas and important information.</p>
                        </div>
                    </div>
                    <div class="card card-success">
                        <div class="card-header">
                            <h5 class="card-title">Success Card</h5>
                        </div>
                        <div class="card-body">
                            <p>Used for positive actions and success states.</p>
                        </div>
                    </div>
                    <div class="card card-warning">
                        <div class="card-header">
                            <h5 class="card-title">Warning Card</h5>
                        </div>
                        <div class="card-body">
                            <p>Used for warnings and caution areas.</p>
                        </div>
                    </div>
                </div>

                <!-- Notifications -->
                <h4 class="mb-3">Notifications</h4>
                <div class="notification notification-success">
                    <span>✅</span>
                    <span>This is a success notification message.</span>
                </div>
                <div class="notification notification-warning">
                    <span>⚠️</span>
                    <span>This is a warning notification message.</span>
                </div>
                <div class="notification notification-error">
                    <span>❌</span>
                    <span>This is an error notification message.</span>
                </div>
                <div class="notification notification-info">
                    <span>ℹ️</span>
                    <span>This is an informational notification message.</span>
                </div>

                <!-- Form Components -->
                <h4 class="mb-3">Form Components</h4>
                <div style="max-width: 400px;">
                    <div class="form-group">
                        <label class="form-label">Text Input</label>
                        <input type="text" class="form-control" placeholder="Enter some text...">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email Input</label>
                        <input type="email" class="form-control" placeholder="Enter your email...">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Select Dropdown</label>
                        <select class="form-control">
                            <option>Choose an option...</option>
                            <option>Option 1</option>
                            <option>Option 2</option>
                            <option>Option 3</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Implementation Guide -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">🛠️ Implementation Guide</h3>
                <p class="card-subtitle">How to apply the new design to existing pages</p>
            </div>
            <div class="card-body">
                <h4 class="mb-3">Quick Start</h4>
                <ol style="line-height: 1.8; color: var(--text-secondary);">
                    <li><strong>Replace CSS:</strong> Change <code>styles.css</code> to <code>styles-modern.css</code> in your pages</li>
                    <li><strong>Add Google Fonts:</strong> Include Inter font from Google Fonts</li>
                    <li><strong>Update HTML Structure:</strong> Use the new card-based layout classes</li>
                    <li><strong>Apply New Classes:</strong> Replace old button classes with new <code>.btn</code> variants</li>
                    <li><strong>Test Responsive:</strong> Ensure mobile compatibility with the new grid system</li>
                </ol>

                <h4 class="mb-3 mt-4">Files Created</h4>
                <ul style="line-height: 1.8; color: var(--text-secondary);">
                    <li><code>styles-modern.css</code> - Complete modern design system</li>
                    <li><code>Views/main-modern.php</code> - New dashboard with card layout</li>
                    <li><code>Views/header-modern.php</code> - Modern header component</li>
                    <li><code>Views/vendors/list_vendors-modern.php</code> - Modern vendor list example</li>
                </ul>

                <div class="mt-4">
                    <a href="<?php echo BASE_URL; ?>DATABASE_SECURITY_UPGRADE.md" class="btn btn-outline btn-sm" target="_blank">View Security Documentation</a>
                    <a href="<?php echo BASE_URL; ?>DATABASE_CONNECTION_FIX.md" class="btn btn-outline btn-sm" target="_blank">View Connection Fix Documentation</a>
                </div>
            </div>
        </div>

        <!-- Color Palette -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">🎨 Color Palette</h3>
                <p class="card-subtitle">Professional color system used throughout the application</p>
            </div>
            <div class="card-body">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                    <div style="text-align: center;">
                        <div style="height: 80px; background: var(--primary-color); border-radius: var(--radius-lg); margin-bottom: 0.5rem;"></div>
                        <strong>Primary Blue</strong><br>
                        <small style="color: var(--text-secondary);">#2563eb</small>
                    </div>
                    <div style="text-align: center;">
                        <div style="height: 80px; background: var(--secondary-color); border-radius: var(--radius-lg); margin-bottom: 0.5rem;"></div>
                        <strong>Secondary Green</strong><br>
                        <small style="color: var(--text-secondary);">#10b981</small>
                    </div>
                    <div style="text-align: center;">
                        <div style="height: 80px; background: var(--accent-color); border-radius: var(--radius-lg); margin-bottom: 0.5rem;"></div>
                        <strong>Accent Amber</strong><br>
                        <small style="color: var(--text-secondary);">#f59e0b</small>
                    </div>
                    <div style="text-align: center;">
                        <div style="height: 80px; background: var(--error-color); border-radius: var(--radius-lg); margin-bottom: 0.5rem;"></div>
                        <strong>Error Red</strong><br>
                        <small style="color: var(--text-secondary);">#ef4444</small>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Add some spacing at the bottom -->
    <div style="height: 2rem;"></div>

    <script>
        function scrollToComponents() {
            document.getElementById('components').scrollIntoView({ behavior: 'smooth' });
        }
    </script>
</body>
</html>
