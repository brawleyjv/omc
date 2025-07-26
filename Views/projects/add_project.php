<?php
require_once __DIR__ . '/../../config.php'; // Updated to use __DIR__
require_once BASE_PATH . '/Models/Database.php'; // Updated to use BASE_PATH
require_once BASE_PATH . '/Controllers/ProjectController.php'; // Updated to use BASE_PATH

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ...existing code...
    header("Location: " . BASE_URL . "Views/projects/list_projects.php"); // Updated to use BASE_URL
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Project - OMC</title>
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
                    <h1>Add New Project</h1>
                    <p>Create a new woodworking project with all the details</p>
                </div>
            </div>
            <nav class="header-nav">
                <a href="<?php echo BASE_URL; ?>Views/main.php" class="nav-link">Dashboard</a>
                <a href="<?php echo BASE_URL; ?>Views/projects/index.php" class="nav-link">Projects Home</a>
                <a href="<?php echo BASE_URL; ?>Views/projects/list_projects.php" class="nav-link">All Projects</a>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-container">
        <!-- Page Header -->
        <div class="page-header">
            <div class="page-header-content">
                <h1 class="page-title">Create New Project</h1>
                <div class="page-actions">
                    <a href="<?php echo BASE_URL; ?>Views/projects/list_projects.php" class="btn btn-ghost">
                        <span class="icon">📋</span>
                        View All Projects
                    </a>
                </div>
            </div>
        </div>

        <!-- Error Messages -->
        <?php if (!empty($_GET['error'])): ?>
            <div class="notification notification-error mb-4">
                <?php echo htmlspecialchars($_GET['error']); ?>
            </div>
        <?php endif; ?>

        <!-- Add Project Form -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Project Details</h2>
                <p class="card-subtitle">Fill in the project information and upload any relevant files</p>
            </div>
            <div class="card-body">
                <form id="project-form" action="<?php echo BASE_URL; ?>public/projects/add_project.php" method="post" enctype="multipart/form-data" onsubmit="return validateForm()">
                    <div class="form-grid">
                        <!-- Basic Information -->
                        <div class="form-section">
                            <h3 class="form-section-title">Basic Information</h3>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="project_name" class="form-label">Project Name *</label>
                                    <input type="text" id="project_name" name="project_name" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label for="customer_name" class="form-label">Customer Name</label>
                                    <input type="text" id="customer_name" name="customer_name" class="form-control">
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="design_date" class="form-label">Design Date *</label>
                                    <input type="date" id="design_date" name="design_date" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label for="due_date" class="form-label">Due Date</label>
                                    <input type="date" id="due_date" name="due_date" class="form-control">
                                </div>
                            </div>
                        </div>

                        <!-- Time Tracking -->
                        <div class="form-section">
                            <h3 class="form-section-title">Time Estimates</h3>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="laser_time" class="form-label">Laser Time (minutes)</label>
                                    <input type="number" id="laser_time" name="laser_time" class="form-control" max="9999" placeholder="0">
                                </div>
                                <div class="form-group">
                                    <label for="router_time" class="form-label">Router Time (minutes)</label>
                                    <input type="number" id="router_time" name="router_time" class="form-control" max="9999" placeholder="0">
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="labor_hours" class="form-label">Labor Hours</label>
                                    <input type="number" id="labor_hours" name="labor_hours" class="form-control" max="9999" placeholder="0" step="0.1">
                                </div>
                            </div>
                        </div>

                        <!-- Project Description -->
                        <div class="form-section">
                            <h3 class="form-section-title">Project Description</h3>
                            
                            <div class="form-group">
                                <label for="project_description" class="form-label">Description</label>
                                <textarea id="project_description" name="project_description" class="form-control" rows="4" placeholder="Describe the project details, materials needed, special requirements..."></textarea>
                            </div>
                        </div>

                        <!-- File Uploads -->
                        <div class="form-section">
                            <h3 class="form-section-title">File Uploads</h3>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="file_upload" class="form-label">Machine Files</label>
                                    <input type="file" id="file_upload" name="file_upload[]" class="form-control" multiple>
                                    <small class="form-text">Upload CNC files, G-code, or other machine files</small>
                                </div>
                                <div class="form-group">
                                    <label for="design_file" class="form-label">Design Files</label>
                                    <input type="file" id="design_file" name="design_file[]" class="form-control" multiple>
                                    <small class="form-text">Upload CAD files, drawings, or design documents</small>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="image_upload" class="form-label">Project Images</label>
                                    <input type="file" id="image_upload" name="image_upload[]" class="form-control" accept=".bmp,.jpg,.jpeg,.tiff,.gif,.png" multiple>
                                    <small class="form-text">Upload reference images, sketches, or photos (BMP, JPG, PNG, etc.)</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <span class="icon">📋</span>
                            Create Project
                        </button>
                        <a href="<?php echo BASE_URL; ?>Views/projects/list_projects.php" class="btn btn-secondary">
                            <span class="icon">✖️</span>
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script>
        function validateForm() {
            var projectName = document.getElementById('project_name').value.trim();
            if (projectName === '') {
                alert('Project name is required.');
                return false;
            }
            return true;
        }
    </script>
</body>
</html>
