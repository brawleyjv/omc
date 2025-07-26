<?php
require_once __DIR__ . '/../../config.php';
require_once BASE_PATH . '/Models/Database.php';
require_once BASE_PATH . '/Controllers/ProjectController.php';

use MyApp\Controllers\ProjectController;
use MyApp\Models\Database;

$database = new Database(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
$db = $database->getConnection();
$controller = new ProjectController($db);

$project_name = $_GET['project_name'] ?? '';
$project = $controller->getProjectByName($project_name);
$success_message = '';
$error_message = '';

if (!$project) {
    header("Location: " . BASE_URL . "Views/projects/index.php?error=" . urlencode('Project not found'));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $updated_project_name = $_POST['project_name'] ?? '';
        $design_date = $_POST['design_date'] ?? '';
        $customer_name = $_POST['customer_name'] ?? '';
        $laser_time = $_POST['laser_time'] ?? 0;
        $router_time = $_POST['router_time'] ?? 0;
        $labor_hours = $_POST['labor_hours'] ?? 0;
        $project_description = $_POST['project_description'] ?? '';
        $due_date = $_POST['due_date'] ?? '';

        $result = $controller->updateProject(
            $project['id'],
            $updated_project_name,
            $design_date,
            $customer_name,
            $laser_time,
            $router_time,
            $labor_hours,
            $project_description,
            $due_date,
            $project['file_upload'],
            $project['image_upload']
        );

        if ($result) {
            header("Location: " . BASE_URL . "Views/projects/index.php?success=" . urlencode('Project updated successfully'));
            exit;
        } else {
            $error_message = 'Failed to update project.';
        }
    } catch (Exception $e) {
        $error_message = 'An error occurred: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Project - OMC</title>
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
                    <h1>Edit Project</h1>
                    <p>Update project information and specifications</p>
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
                <h1 class="page-title">Edit Project</h1>
                <div class="page-actions">
                    <a href="<?php echo BASE_URL; ?>Views/projects/index.php" class="btn btn-secondary">
                        <span class="icon">📋</span>
                        Back to Projects
                    </a>
                </div>
            </div>
        </div>

        <!-- Error Message -->
        <?php if (!empty($error_message)): ?>
            <div class="notification notification-error">
                <p><strong>Error:</strong> <?php echo htmlspecialchars($error_message); ?></p>
            </div>
        <?php endif; ?>

        <!-- Edit Form -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Update Project Information</h2>
                <p class="card-subtitle">Modify project details, timeline, and specifications</p>
            </div>
            <div class="card-body">
                <form action="<?php echo BASE_URL; ?>Views/projects/edit_project.php?project_name=<?php echo urlencode($project_name); ?>" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="original_project_name" value="<?php echo htmlspecialchars($project['project_name']); ?>">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Basic Information -->
                        <div class="form-group">
                            <label for="project_name" class="form-label required">Project Name</label>
                            <input type="text" id="project_name" name="project_name" class="form-control" 
                                   value="<?php echo htmlspecialchars($project['project_name']); ?>" required
                                   placeholder="Enter project name...">
                            <small class="form-text">Unique project identifier</small>
                        </div>

                        <div class="form-group">
                            <label for="customer_name" class="form-label">Customer Name</label>
                            <input type="text" id="customer_name" name="customer_name" class="form-control" 
                                   value="<?php echo htmlspecialchars($project['customer_name'] ?? ''); ?>"
                                   placeholder="Enter customer name...">
                            <small class="form-text">Client or customer for this project</small>
                        </div>

                        <!-- Timeline -->
                        <div class="form-group">
                            <label for="design_date" class="form-label required">Design Date</label>
                            <input type="date" id="design_date" name="design_date" class="form-control" 
                                   value="<?php echo htmlspecialchars($project['design_date']); ?>" required>
                            <small class="form-text">Date the project was designed</small>
                        </div>

                        <div class="form-group">
                            <label for="due_date" class="form-label">Due Date</label>
                            <input type="date" id="due_date" name="due_date" class="form-control" 
                                   value="<?php echo htmlspecialchars($project['due_date'] ?? ''); ?>">
                            <small class="form-text">Project completion deadline</small>
                        </div>

                        <!-- Time Estimates -->
                        <div class="form-group">
                            <label for="laser_time" class="form-label">Laser Time (minutes)</label>
                            <input type="number" id="laser_time" name="laser_time" class="form-control" 
                                   value="<?php echo htmlspecialchars($project['laser_time'] ?? ''); ?>" 
                                   min="0" max="9999" placeholder="0">
                            <small class="form-text">Estimated laser cutting time</small>
                        </div>

                        <div class="form-group">
                            <label for="router_time" class="form-label">Router Time (minutes)</label>
                            <input type="number" id="router_time" name="router_time" class="form-control" 
                                   value="<?php echo htmlspecialchars($project['router_time'] ?? ''); ?>" 
                                   min="0" max="9999" placeholder="0">
                            <small class="form-text">Estimated CNC router time</small>
                        </div>

                        <div class="form-group">
                            <label for="labor_hours" class="form-label">Labor Hours</label>
                            <input type="number" id="labor_hours" name="labor_hours" class="form-control" 
                                   value="<?php echo htmlspecialchars($project['labor_hours'] ?? ''); ?>" 
                                   min="0" max="9999" step="0.5" placeholder="0">
                            <small class="form-text">Estimated manual labor hours</small>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="form-group mt-6">
                        <label for="project_description" class="form-label">Project Description</label>
                        <textarea id="project_description" name="project_description" class="form-control" rows="4"
                                  placeholder="Enter detailed project description..."><?php echo htmlspecialchars($project['project_description'] ?? ''); ?></textarea>
                        <small class="form-text">Detailed description of the project requirements</small>
                    </div>

                    <!-- Current Files Display -->
                    <?php if (!empty($project['file_upload']) || !empty($project['image_upload'])): ?>
                        <div class="form-section mt-6">
                            <h3 class="form-section-title">Current Project Files</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <?php if (!empty($project['file_upload'])): ?>
                                    <div class="file-display">
                                        <h4 class="font-medium mb-2">Machine Files</h4>
                                        <div class="file-list">
                                            <?php
                                            $files = explode(',', $project['file_upload']);
                                            foreach ($files as $file) {
                                                $file = trim($file);
                                                if (!empty($file)) {
                                                    $file_path = BASE_URL . "projects/project_files/{$project['project_name']}/{$file}";
                                                    echo "<div class='file-item'>";
                                                    echo "<a href='{$file_path}' download class='text-link'>📄 " . htmlspecialchars($file) . "</a>";
                                                    echo "</div>";
                                                }
                                            }
                                            ?>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($project['image_upload'])): ?>
                                    <div class="file-display">
                                        <h4 class="font-medium mb-2">Project Images</h4>
                                        <div class="image-list">
                                            <?php
                                            $images = explode(',', $project['image_upload']);
                                            foreach ($images as $image) {
                                                $image = trim($image);
                                                if (!empty($image)) {
                                                    $image_path = BASE_URL . "projects/project_files/{$project['project_name']}/{$image}";
                                                    echo "<div class='image-item'>";
                                                    echo "<img src='{$image_path}' alt='Project Image' class='image-thumbnail' onclick=\"openImage('{$image_path}')\">";
                                                    echo "</div>";
                                                }
                                            }
                                            ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="form-actions mt-6">
                        <button type="submit" class="btn btn-primary">
                            <span class="icon">💾</span>
                            Update Project
                        </button>
                        <a href="<?php echo BASE_URL; ?>Views/projects/index.php" class="btn btn-secondary">
                            <span class="icon">❌</span>
                            Cancel
                        </a>
                        <button type="button" onclick="confirmDelete('<?php echo htmlspecialchars($project['project_name']); ?>')" class="btn btn-danger">
                            <span class="icon">🗑️</span>
                            Delete Project
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script>
        function confirmDelete(projectName) {
            if (confirm('Are you sure you want to delete this project? This action cannot be undone and will delete all associated files.')) {
                window.location.href = '<?php echo BASE_URL; ?>public/projects/delete_project.php?project_name=' + encodeURIComponent(projectName);
            }
        }

        function openImage(url) {
            const imgWindow = window.open("", "_blank", "width=800,height=600");
            imgWindow.document.write(`
                <html>
                <head>
                    <title>Project Image Viewer</title>
                    <style>
                        body { margin: 0; padding: 20px; background: #f0f0f0; display: flex; justify-content: center; align-items: center; min-height: 100vh; }
                        img { max-width: 100%; max-height: 100%; object-fit: contain; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.3); }
                    </style>
                </head>
                <body>
                    <img src="${url}" alt="Project Image" onclick="window.close()">
                </body>
                </html>
            `);
        }
    </script>

    <style>
        .file-display {
            background: var(--color-background-muted);
            padding: 1rem;
            border-radius: 8px;
            border: 1px solid var(--color-border);
        }

        .file-list, .image-list {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .file-item {
            padding: 0.25rem 0;
        }

        .image-item {
            margin-right: 0.5rem;
            margin-bottom: 0.5rem;
        }

        .image-thumbnail {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 4px;
            cursor: pointer;
            border: 1px solid var(--color-border);
        }

        .image-thumbnail:hover {
            transform: scale(1.05);
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }
    </style>
</body>
</html>
