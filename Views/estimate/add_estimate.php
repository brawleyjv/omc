<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include the header and ensure BASE_PATH is defined
require_once realpath(dirname(__FILE__) . '/../../config.php');

// Initialize variables to avoid warnings
$projects = $projects ?? [];
$selectedProject = $selectedProject ?? null;
$bomMaterials = $bomMaterials ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Estimate - OMC</title>
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
                    <h1>Create Estimate</h1>
                    <p>Generate project cost estimates and quotations</p>
                </div>
            </div>
            <nav class="header-nav">
                <a href="<?php echo BASE_URL; ?>Views/main.php" class="nav-link">Dashboard</a>
                <a href="<?php echo BASE_URL; ?>Views/estimate/estimate.php" class="nav-link">Estimates Home</a>
                <a href="<?php echo BASE_URL; ?>Views/projects/list_projects.php" class="nav-link">Projects</a>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-container">
        <!-- Page Header -->
        <div class="page-header">
            <div class="page-header-content">
                <h1 class="page-title">New Project Estimate</h1>
                <div class="page-actions">
                    <a href="<?php echo BASE_URL; ?>Views/estimate/estimate.php" class="btn btn-ghost">
                        <span class="icon">📊</span>
                        All Estimates
                    </a>
                </div>
            </div>
        </div>
        <!-- Estimate Creation Form -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Project Estimate</h2>
                <p class="card-subtitle">Search for a project to create an estimate</p>
            </div>
            <div class="card-body">
                <?php if (!empty($errorMessage)): ?>
                    <div class="notification notification-error">
                        <?php echo htmlspecialchars($errorMessage); ?>
                    </div>
                <?php endif; ?>

                <!-- Project Search Form -->
                <div class="form-section">
                    <h3 class="form-section-title">Find Project</h3>
                    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="project_search" class="form-label">Search Project</label>
                                <input type="text" id="project_search" name="project_search" class="form-control" 
                                       placeholder="Enter project name or description">
                            </div>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <span class="icon">🔍</span>
                                Search Projects
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Display Matching Projects -->
                <?php if (!empty($matchingProjects)): ?>
                    <div class="form-section">
                        <h3 class="form-section-title">Matching Projects</h3>
                        <div class="table-container">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Project Name</th>
                                        <th>Description</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($matchingProjects as $project): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($project['project_name']); ?></td>
                                            <td><?php echo htmlspecialchars($project['project_description']); ?></td>
                                            <td>
                                                <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" style="display: inline;">
                                                    <button type="submit" name="select_project" value="<?php echo htmlspecialchars($project['id']); ?>" class="btn btn-primary btn-sm">
                                                        Select
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($matchingProjects)): ?>
                    <div class="notification notification-warning">
                        No projects found matching your search criteria. Please try again.
                    </div>
                <?php endif; ?>

                <!-- Project List -->
                <?php if (!empty($projects)): ?>
                    <div class="form-section">
                        <h3 class="form-section-title">Available Projects</h3>
                        <div class="grid grid-cols-1 gap-3">
                            <?php foreach ($projects as $project): ?>
                                <div class="card card-hover" onclick="selectProject(<?php echo htmlspecialchars(json_encode($project)); ?>)">
                                    <div class="card-body">
                                        <h4 class="card-title"><?php echo htmlspecialchars($project['project_name']); ?></h4>
                                        <p class="card-subtitle"><?php echo htmlspecialchars($project['project_description'] ?? 'No description'); ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Selected Project Details -->
                <?php if ($selectedProject): ?>
                    <div class="form-section">
                        <h3 class="form-section-title">Project Details</h3>
                        <div class="info-grid">
                            <div class="info-item">
                                <label class="info-label">Project Name:</label>
                                <span class="info-value"><?php echo htmlspecialchars($selectedProject['project_name'] ?? 'Not Available'); ?></span>
                            </div>
                            <div class="info-item">
                                <label class="info-label">Description:</label>
                                <span class="info-value"><?php echo htmlspecialchars($selectedProject['project_description'] ?? 'Not Available'); ?></span>
                            </div>
                            <div class="info-item">
                                <label class="info-label">Router Time:</label>
                                <span class="info-value"><?php echo htmlspecialchars($selectedProject['router_time'] ?? 'Not Available'); ?> minutes</span>
                            </div>
                            <div class="info-item">
                                <label class="info-label">Laser Time:</label>
                                <span class="info-value"><?php echo htmlspecialchars($selectedProject['laser_time'] ?? 'Not Available'); ?> minutes</span>
                            </div>
                        </div>
                    </div>

                    <!-- BOM Details -->
                    <div class="form-section">
                        <h3 class="form-section-title">Bill of Materials</h3>
                        <?php if (!empty($bomMaterials)): ?>
                            <div class="table-container">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Material</th>
                                            <th>Length</th>
                                            <th>Width</th>
                                            <th>Thickness</th>
                                            <th>Quantity</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($bomMaterials as $material): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($material['material_name'] ?? ''); ?></td>
                                                <td><?php echo htmlspecialchars($material['length'] ?? ''); ?></td>
                                                <td><?php echo htmlspecialchars($material['width'] ?? ''); ?></td>
                                                <td><?php echo htmlspecialchars($material['thickness'] ?? ''); ?></td>
                                                <td><?php echo htmlspecialchars($material['quantity'] ?? ''); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="notification notification-info">
                                No BOM found for this project.
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Estimate Form -->
                    <div class="form-section">
                        <h3 class="form-section-title">Create Estimate</h3>
                        <form action="<?php echo BASE_URL; ?>public/Estimate/add_estimate.php" method="post">
                            <input type="hidden" name="project_id" value="<?php echo htmlspecialchars($selectedProject['id'] ?? ''); ?>">
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="project_name" class="form-label">Project Name</label>
                                    <input type="text" id="project_name" name="project_name" class="form-control" 
                                           value="<?php echo htmlspecialchars($selectedProject['project_name'] ?? ''); ?>" readonly>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="router_time" class="form-label">Router Time (minutes)</label>
                                    <input type="number" id="router_time" name="router_time" class="form-control" 
                                           value="<?php echo htmlspecialchars($selectedProject['router_time'] ?? ''); ?>" readonly>
                                </div>
                                <div class="form-group">
                                    <label for="laser_time" class="form-label">Laser Time (minutes)</label>
                                    <input type="number" id="laser_time" name="laser_time" class="form-control" 
                                           value="<?php echo htmlspecialchars($selectedProject['laser_time'] ?? ''); ?>" readonly>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="labor_time" class="form-label">Labor Time (minutes)</label>
                                    <input type="number" id="labor_time" name="labor_time" class="form-control" 
                                           value="<?php echo htmlspecialchars($selectedProject['labor_time'] ?? ''); ?>" readonly>
                                </div>
                            </div>

                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">
                                    <span class="icon">📊</span>
                                    Generate Estimate
                                </button>
                                <a href="<?php echo BASE_URL; ?>Views/estimate/estimate.php" class="btn btn-secondary">
                                    <span class="icon">✖️</span>
                                    Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Hidden Form for Project Selection -->
        <form id="select-project-form" action="<?php echo BASE_URL; ?>public/Estimate/add_estimate.php" method="post" style="display: none;">
            <input type="hidden" id="hidden_project_id" name="project_id">
            <input type="hidden" id="hidden_project_name" name="project_name">
            <input type="hidden" id="hidden_project_description" name="project_description">
            <input type="hidden" id="hidden_router_time" name="router_time">
            <input type="hidden" id="hidden_laser_time" name="laser_time">
        </form>
    </main>

    <script>
        function selectProject(project) {
            document.getElementById('hidden_project_id').value = project.id;
            document.getElementById('hidden_project_name').value = project.project_name;
            document.getElementById('hidden_project_description').value = project.project_description;
            document.getElementById('hidden_router_time').value = project.router_time;
            document.getElementById('hidden_laser_time').value = project.laser_time;
            document.getElementById('select-project-form').submit();
        }
    </script>
</body>
</html>
            document.getElementById('laser_time').value = project.laser_time;
            document.getElementById('select-project-form').submit();
        }
    </script>
</body>
</html>
