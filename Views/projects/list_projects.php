<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config.php'; // Updated to use __DIR__
require_once BASE_PATH . '/Models/Database.php'; // Updated to use BASE_PATH
require_once BASE_PATH . '/Controllers/ProjectController.php'; // Updated to use BASE_PATH

use MyApp\Controllers\ProjectController;
use MyApp\Models\Database;

$database = new Database(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
$db = $database->getConnection(); // Ensure $db is a PDO instance
$projectsController = new ProjectController($db); // Pass the PDO instance to the controller

// Handle production status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_production_status'])) {
    $projectId = $_POST['project_id'];
    $newStatus = $_POST['production_status'];
    
    // If changing to 'ready' or 'active', validate that an estimate exists
    if (in_array($newStatus, ['ready', 'active'])) {
        $checkQuery = "SELECT p.estimate_id, e.total_estimate 
                       FROM projects p 
                       LEFT JOIN estimates e ON p.estimate_id = e.id 
                       WHERE p.id = :id";
        $checkStmt = $db->prepare($checkQuery);
        $checkStmt->execute([':id' => $projectId]);
        $projectData = $checkStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$projectData['estimate_id'] || !$projectData['total_estimate']) {
            header("Location: " . BASE_URL . "Views/projects/list_projects.php?error=no_estimate");
            exit;
        }
        
        // Update cost_per_unit from estimate when going to ready/active
        $updateQuery = "UPDATE projects 
                        SET production_status = :status,
                            cost_per_unit = (SELECT total_estimate FROM estimates WHERE id = :estimate_id)
                        WHERE id = :id";
        $updateStmt = $db->prepare($updateQuery);
        $updateStmt->execute([
            ':status' => $newStatus,
            ':estimate_id' => $projectData['estimate_id'],
            ':id' => $projectId
        ]);
    } else {
        // For 'design' or 'discontinued', just update status
        $updateQuery = "UPDATE projects SET production_status = :status WHERE id = :id";
        $updateStmt = $db->prepare($updateQuery);
        $updateStmt->execute([
            ':status' => $newStatus,
            ':id' => $projectId
        ]);
    }
    
    header("Location: " . BASE_URL . "Views/projects/list_projects.php?updated=1");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_project_name'])) {
    $projectName = $_POST['delete_project_name'];
    error_log("Deleting project with name: $projectName"); // Log the project name being deleted
    $projectsController->deleteProjectByName($projectName);
    header("Location: " . BASE_URL . "Views/projects/list_projects.php"); // Updated to use BASE_URL
    exit;
}

// Fetch projects with their estimate information
$query = "SELECT p.*, 
          e.id as estimate_id, 
          e.estimate_number, 
          e.total_estimate, 
          e.status as estimate_status 
          FROM projects p 
          LEFT JOIN estimates e ON p.estimate_id = e.id 
          ORDER BY p.design_date DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>List Projects - OMC</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>styles-modern.css">
    <script>
        function openImage(url) {
            if (!url || url.trim() === '') {
                alert('No image URL available');
                return;
            }
            
            const imgWindow = window.open("", "_blank", "width=800,height=600");
            imgWindow.document.write(`
                <html>
                <head>
                    <title>Project Image Viewer</title>
                    <style>
                        body { margin: 0; display: flex; justify-content: center; align-items: center; height: 100vh; background-color: #000; }
                        img { max-width: 100%; max-height: 100%; }
                        .close-button {
                            position: absolute;
                            top: 10px;
                            right: 10px;
                            background-color: #DC3545;
                            color: white;
                            border: none;
                            padding: 10px;
                            cursor: pointer;
                            font-size: 16px;
                            border-radius: 5px;
                        }
                        .close-button:hover {
                            background-color: #c82333;
                        }
                        .error-message {
                            color: white;
                            text-align: center;
                            font-family: Arial, sans-serif;
                        }
                    </style>
                </head>
                <body>
                    <button class="close-button" onclick="window.close()">Close</button>
                    <img src="${url}" alt="Project Image" 
                         onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                    <div class="error-message" style="display:none;">
                        <p>Image could not be loaded.</p>
                        <p><a href="${url}" target="_blank" style="color: #007bff;">Open image in new tab</a></p>
                    </div>
                </body>
                </html>
            `);
        }
    </script>
</head>
<body>
    <!-- Modern Header -->
    <header class="header">
        <div class="header-content">
            <div class="header-brand">
                <div class="header-brand-text">
                    <h1>Project Management</h1>
                    <p>Track and manage your woodworking projects</p>
                </div>
            </div>
            <nav class="header-nav">
                <a href="<?php echo BASE_URL; ?>Views/main.php" class="nav-link">Dashboard</a>
                <a href="<?php echo BASE_URL; ?>Views/projects/index.php" class="nav-link">Projects Home</a>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-container">
        <!-- Page Header -->
        <div class="page-header">
            <div class="page-header-content">
                <h1 class="page-title">Project List</h1>
                <div class="page-actions">
                    <a href="<?php echo BASE_URL; ?>Views/projects/add_project.php" class="btn btn-primary">
                        <span class="icon">📋</span>
                        Add Project
                    </a>
                </div>
            </div>
        </div>

        <!-- Production Status Info -->
        <div class="card" style="background-color: #f0f9ff; border-left: 4px solid #3b82f6;">
            <div class="card-body">
                <h3 style="margin: 0 0 0.5rem 0; font-size: 1rem; color: #1e40af;">📊 Production Status Guide</h3>
                
                <?php if (isset($_GET['error']) && $_GET['error'] === 'no_estimate'): ?>
                    <div style="background: #fef2f2; border: 2px solid #dc2626; padding: 1rem; border-radius: 6px; margin-bottom: 1rem;">
                        <strong style="color: #991b1b;">⚠️ Cannot Change Status:</strong>
                        <span style="color: #7f1d1d;">This project requires an estimate before it can be marked as Ready or Active. Please create an estimate first.</span>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_GET['updated']) && $_GET['updated'] === '1'): ?>
                    <div style="background: #f0fdf4; border: 2px solid #10b981; padding: 1rem; border-radius: 6px; margin-bottom: 1rem;">
                        <strong style="color: #065f46;">✓ Status Updated:</strong>
                        <span style="color: #064e3b;">Production status has been successfully changed.</span>
                    </div>
                <?php endif; ?>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; font-size: 0.875rem;">
                    <div>
                        <strong style="color: #6b7280;">📝 Design</strong>
                        <div style="color: #4b5563; margin-top: 0.25rem;">
                            Initial phase: designing, testing, creating estimates. <strong>Not tracked in inventory.</strong>
                        </div>
                    </div>
                    <div>
                        <strong style="color: #059669;">✅ Ready</strong>
                        <div style="color: #4b5563; margin-top: 0.25rem;">
                            Approved design, ready to produce. <strong>Requires estimate. Shows in inventory.</strong>
                        </div>
                    </div>
                    <div>
                        <strong style="color: #dc2626;">🏭 Active</strong>
                        <div style="color: #4b5563; margin-top: 0.25rem;">
                            Regular production item. <strong>Requires estimate. Fully tracked and monitored.</strong>
                        </div>
                    </div>
                    <div>
                        <strong style="color: #6b7280;">⛔ Discontinued</strong>
                        <div style="color: #4b5563; margin-top: 0.25rem;">
                            No longer producing. <strong>Hidden from inventory (unless stock remains).</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Projects Table Card -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">All Projects</h2>
                <p class="card-subtitle">Manage your woodworking projects from design to completion</p>
            </div>
            <div class="card-body">
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Project</th>
                                <th>Customer</th>
                                <th>Production Status</th>
                                <th>Timeline</th>
                                <th>Hours</th>
                                <th>Estimate</th>
                                <th>Description</th>
                                <th>Files</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($projects as $project): ?>
                                <tr>
                                    <td>
                                        <div class="project-info">
                                            <strong><?php echo htmlspecialchars($project['project_name']); ?></strong>
                                            <div class="text-sm text-muted">
                                                Created: <?php echo htmlspecialchars($project['design_date']); ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="customer-info">
                                            <?php if (!empty($project['customer_name'])): ?>
                                                <span class="badge badge-info"><?php echo htmlspecialchars($project['customer_name']); ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">No customer assigned</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <form method="POST" style="display: inline-block;">
                                            <input type="hidden" name="project_id" value="<?php echo $project['id']; ?>">
                                            <input type="hidden" name="update_production_status" value="1">
                                            <select name="production_status" class="form-control" style="font-size: 0.875rem; padding: 0.25rem 0.5rem;" onchange="this.form.submit()" title="Change production status">
                                                <option value="design" <?php echo ($project['production_status'] ?? 'design') === 'design' ? 'selected' : ''; ?>>
                                                    📝 Design (Prototype/Testing)
                                                </option>
                                                <option value="ready" <?php echo ($project['production_status'] ?? '') === 'ready' ? 'selected' : ''; ?>>
                                                    ✅ Ready (Approved for Production)
                                                </option>
                                                <option value="active" <?php echo ($project['production_status'] ?? '') === 'active' ? 'selected' : ''; ?>>
                                                    🏭 Active (Currently Producing)
                                                </option>
                                                <option value="discontinued" <?php echo ($project['production_status'] ?? '') === 'discontinued' ? 'selected' : ''; ?>>
                                                    ⛔ Discontinued (No Longer Made)
                                                </option>
                                            </select>
                                        </form>
                                        <div style="font-size: 0.75rem; color: #6b7280; margin-top: 0.25rem;">
                                            <?php 
                                            $status = $project['production_status'] ?? 'design';
                                            $descriptions = [
                                                'design' => 'Not tracked in inventory',
                                                'ready' => 'Shows in inventory, can produce',
                                                'active' => 'Regular production, tracked',
                                                'discontinued' => 'No longer producing'
                                            ];
                                            echo $descriptions[$status] ?? '';
                                            ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="timeline-info">
                                            <?php if (!empty($project['due_date'])): ?>
                                                <div class="text-sm">
                                                    <span class="icon">📅</span>
                                                    Due: <?php echo htmlspecialchars($project['due_date']); ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="hours-info">
                                            <?php if (!empty($project['laser_time'])): ?>
                                                <div class="text-sm">⚡ Laser: <?php echo htmlspecialchars($project['laser_time']); ?>h</div>
                                            <?php endif; ?>
                                            <?php if (!empty($project['router_time'])): ?>
                                                <div class="text-sm">🔧 Router: <?php echo htmlspecialchars($project['router_time']); ?>h</div>
                                            <?php endif; ?>
                                            <?php if (!empty($project['labor_hours'])): ?>
                                                <div class="text-sm">👷 Labor: <?php echo htmlspecialchars($project['labor_hours']); ?>h</div>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="estimate-info">
                                            <?php if (!empty($project['estimate_id'])): ?>
                                                <div class="text-sm">
                                                    <a href="<?php echo BASE_URL; ?>Views/estimate/view_estimate.php?id=<?php echo $project['estimate_id']; ?>" class="text-link">
                                                        <?php echo htmlspecialchars($project['estimate_number']); ?>
                                                    </a>
                                                </div>
                                                <?php if (!empty($project['total_estimate'])): ?>
                                                    <div class="text-sm text-success">
                                                        💰 $<?php echo number_format($project['total_estimate'], 2); ?>
                                                    </div>
                                                <?php endif; ?>
                                                <?php if (!empty($project['estimate_status'])): ?>
                                                    <span class="badge badge-<?php 
                                                        echo $project['estimate_status'] === 'approved' ? 'success' : 
                                                             ($project['estimate_status'] === 'sent' ? 'info' : 
                                                             ($project['estimate_status'] === 'rejected' ? 'danger' : 'secondary')); 
                                                    ?>">
                                                        <?php echo ucfirst($project['estimate_status']); ?>
                                                    </span>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <a href="<?php echo BASE_URL; ?>Views/estimate/create_from_project.php?project_id=<?php echo $project['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                    <span class="icon">💰</span> Create Estimate
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if (!empty($project['project_description'])): ?>
                                            <span class="text-truncate" title="<?php echo htmlspecialchars($project['project_description']); ?>">
                                                <?php echo htmlspecialchars(substr($project['project_description'], 0, 60)) . (strlen($project['project_description']) > 60 ? '...' : ''); ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">No description</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="files-info">
                                            <!-- Machine Files -->
                                            <?php if (!empty($project['file_upload'])): ?>
                                                <?php
                                                $file_uploads = explode(',', $project['file_upload']);
                                                $file_count = count($file_uploads);
                                                $project_id = $project['id'] ?? $project['project_name']; // Use project ID or name for unique identifiers
                                                ?>
                                                
                                                <!-- Always show first file -->
                                                <?php if ($file_count > 0): ?>
                                                    <?php
                                                    $first_file = trim($file_uploads[0]);
                                                    if (!empty($first_file)) {
                                                        $file_upload_label = pathinfo($first_file, PATHINFO_FILENAME);
                                                        $file_upload_path = BASE_URL . "projects/project_files/{$project['project_name']}/{$first_file}";
                                                        echo "<div class='text-sm'><a href='{$file_upload_path}' download class='text-link'>📄 {$file_upload_label}</a></div>";
                                                    }
                                                    ?>
                                                <?php endif; ?>
                                                
                                                <!-- Show second file if exists -->
                                                <?php if ($file_count > 1): ?>
                                                    <?php
                                                    $second_file = trim($file_uploads[1]);
                                                    if (!empty($second_file)) {
                                                        $file_upload_label = pathinfo($second_file, PATHINFO_FILENAME);
                                                        $file_upload_path = BASE_URL . "projects/project_files/{$project['project_name']}/{$second_file}";
                                                        echo "<div class='text-sm'><a href='{$file_upload_path}' download class='text-link'>📄 {$file_upload_label}</a></div>";
                                                    }
                                                    ?>
                                                <?php endif; ?>
                                                
                                                <!-- Show expandable section for additional files -->
                                                <?php if ($file_count > 2): ?>
                                                    <div class="text-sm">
                                                        <button type="button" class="btn-link text-muted" onclick="toggleFiles('files-<?php echo $project_id; ?>')">
                                                            <span id="files-toggle-<?php echo $project_id; ?>">+<?php echo $file_count - 2; ?> more files</span>
                                                        </button>
                                                    </div>
                                                    <div id="files-<?php echo $project_id; ?>" class="additional-files" style="display: none;">
                                                        <?php
                                                        for ($i = 2; $i < $file_count; $i++) {
                                                            $file_upload = trim($file_uploads[$i]);
                                                            if (!empty($file_upload)) {
                                                                $file_upload_label = pathinfo($file_upload, PATHINFO_FILENAME);
                                                                $file_upload_path = BASE_URL . "projects/project_files/{$project['project_name']}/{$file_upload}";
                                                                echo "<div class='text-sm'><a href='{$file_upload_path}' download class='text-link'>📄 {$file_upload_label}</a></div>";
                                                            }
                                                        }
                                                        ?>
                                                    </div>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                            
                                            <!-- Project Images -->
                                            <?php if (!empty($project['image_upload'])): ?>
                                                <?php
                                                $image_uploads = explode(',', $project['image_upload']);
                                                $image_count = count($image_uploads);
                                                $first_image_upload = trim($image_uploads[0]);
                                                
                                                if (!empty($first_image_upload)) {
                                                    $project_name_encoded = urlencode($project['project_name']);
                                                    $image_upload_path = BASE_URL . "projects/project_files/" . $project_name_encoded . "/" . urlencode($first_image_upload);
                                                    $image_display_path = BASE_URL . "projects/project_files/{$project['project_name']}/{$first_image_upload}";
                                                    ?>
                                                    <div class='mt-1'>
                                                        <!-- Thumbnail Image -->
                                                        <div class="image-thumbnail-container" style="margin-bottom: 0.5rem;">
                                                            <img src="<?php echo htmlspecialchars($image_display_path); ?>" 
                                                                 alt="<?php echo htmlspecialchars($project['project_name']); ?> Image" 
                                                                 class="image-thumbnail"
                                                                 onclick="openImage('<?php echo htmlspecialchars($image_display_path); ?>')"
                                                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='inline-block';"
                                                                 title="Click to view full size: <?php echo htmlspecialchars($first_image_upload); ?>">
                                                            <button onclick="openImage('<?php echo htmlspecialchars($image_display_path); ?>')" 
                                                                    class="btn btn-ghost btn-sm" 
                                                                    style="display: none;"
                                                                    title="View project image: <?php echo htmlspecialchars($first_image_upload); ?>">
                                                                🖼️ View Image
                                                            </button>
                                                        </div>
                                                        
                                                        <!-- Additional Images -->
                                                        <?php if ($image_count > 1): ?>
                                                            <div class="text-sm">
                                                                <button type="button" class="btn-link text-muted" onclick="toggleFiles('images-<?php echo $project_id; ?>')">
                                                                    <span id="images-toggle-<?php echo $project_id; ?>">+<?php echo $image_count - 1; ?> more images</span>
                                                                </button>
                                                            </div>
                                                            <div id="images-<?php echo $project_id; ?>" class="additional-images" style="display: none;">
                                                                <?php
                                                                for ($i = 1; $i < $image_count; $i++) {
                                                                    $image_upload = trim($image_uploads[$i]);
                                                                    if (!empty($image_upload)) {
                                                                        $image_display_path = BASE_URL . "projects/project_files/{$project['project_name']}/{$image_upload}";
                                                                        $image_name = pathinfo($image_upload, PATHINFO_FILENAME);
                                                                        ?>
                                                                        <div class="image-thumbnail-container" style="margin-bottom: 0.25rem;">
                                                                            <img src="<?php echo htmlspecialchars($image_display_path); ?>" 
                                                                                 alt="<?php echo htmlspecialchars($project['project_name']); ?> Image <?php echo $i + 1; ?>" 
                                                                                 class="image-thumbnail small"
                                                                                 onclick="openImage('<?php echo htmlspecialchars($image_display_path); ?>')"
                                                                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='inline-block';"
                                                                                 title="Click to view: <?php echo htmlspecialchars($image_upload); ?>">
                                                                            <button onclick="openImage('<?php echo htmlspecialchars($image_display_path); ?>')" 
                                                                                    class="btn btn-ghost btn-xs" 
                                                                                    style="display: none;"
                                                                                    title="View: <?php echo htmlspecialchars($image_name); ?>">
                                                                                🖼️ <?php echo htmlspecialchars($image_name); ?>
                                                                            </button>
                                                                        </div>
                                                                        <?php
                                                                    }
                                                                }
                                                                ?>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                    <?php
                                                }
                                                ?>
                                            <?php else: ?>
                                                <div class="text-sm text-muted">No images</div>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="<?php echo BASE_URL; ?>Views/projects/edit_project.php?project_name=<?php echo urlencode($project['project_name']); ?>" 
                                               class="btn btn-ghost btn-sm" title="Edit Project">
                                                <span class="icon">✏️</span>
                                                Edit
                                            </a>
                                            <form action="<?php echo BASE_URL; ?>public/projects/list_projects.php" method="post" 
                                                  onsubmit="return confirm('Are you sure you want to delete this project?');" 
                                                  style="display:inline;">
                                                <input type="hidden" name="delete_project_name" value="<?php echo htmlspecialchars($project['project_name']); ?>">
                                                <button type="submit" class="btn btn-danger btn-sm" title="Delete Project">
                                                    <span class="icon">🗑️</span>
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <script>
        function toggleFiles(elementId) {
            const element = document.getElementById(elementId);
            const isHidden = element.style.display === 'none';
            
            if (isHidden) {
                element.style.display = 'block';
                // Update toggle text to show "Hide"
                const toggleElement = document.getElementById(elementId.replace('files-', 'files-toggle-').replace('images-', 'images-toggle-'));
                if (toggleElement) {
                    const currentText = toggleElement.textContent;
                    toggleElement.textContent = 'Hide additional ' + (elementId.includes('files-') ? 'files' : 'images');
                }
            } else {
                element.style.display = 'none';
                // Restore original toggle text
                const toggleElement = document.getElementById(elementId.replace('files-', 'files-toggle-').replace('images-', 'images-toggle-'));
                if (toggleElement) {
                    if (elementId.includes('files-')) {
                        const fileCount = element.children.length;
                        toggleElement.textContent = '+' + fileCount + ' more files';
                    } else {
                        const imageCount = element.children.length;
                        toggleElement.textContent = '+' + imageCount + ' more images';
                    }
                }
            }
        }
    </script>

    <style>
        .btn-link {
            background: none;
            border: none;
            color: var(--color-primary);
            text-decoration: underline;
            cursor: pointer;
            font-size: inherit;
            padding: 0;
        }
        
        .btn-link:hover {
            color: var(--color-primary-dark);
        }
        
        .additional-files,
        .additional-images {
            margin-top: 0.25rem;
            padding-top: 0.25rem;
            border-top: 1px solid var(--color-border-light);
        }
        
        .image-thumbnail.small {
            width: 30px;
            height: 30px;
            object-fit: cover;
            border-radius: 4px;
        }
        
        .image-thumbnail-container {
            display: inline-block;
            margin-right: 0.5rem;
        }
    </style>
</body>
</html>