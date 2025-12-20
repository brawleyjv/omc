<?php
require_once realpath(dirname(__FILE__) . '/../config.php');

try {
    $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASSWORD);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $search_term = isset($_GET['search_term']) ? $_GET['search_term'] : '';

    $projects_result = null;
    if (!empty($search_term)) {
        // Fetch projects based on search term
        $projects_sql = "SELECT * FROM projects WHERE project_name LIKE :search_term OR customer_name LIKE :search_term OR project_description LIKE :search_term";
        $stmt = $conn->prepare($projects_sql);
        $stmt->execute(['search_term' => '%' . $search_term . '%']);
        $projects_result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Projects - OMC</title>
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
                    <h1>Search Projects</h1>
                    <p>Find projects by name, customer, or description</p>
                </div>
            </div>
            <nav class="header-nav">
                <a href="<?php echo BASE_URL; ?>Views/main.php" class="nav-link">Dashboard</a>
                <a href="<?php echo BASE_URL; ?>Views/projects/list_projects.php" class="nav-link">All Projects</a>
                <a href="<?php echo BASE_URL; ?>Views/projects/add_project.php" class="nav-link">Add Project</a>
            </nav>
        </div>
    </header>
    <!-- Main Content -->
    <main class="main-container">
        <!-- Page Header -->
        <div class="page-header">
            <div class="page-header-content">
                <h1 class="page-title">Project Search</h1>
                <div class="page-actions">
                    <a href="<?php echo BASE_URL; ?>Views/projects/add_project.php" class="btn btn-primary">
                        <span class="icon">📋</span>
                        Add New Project
                    </a>
                </div>
            </div>
        </div>

        <!-- Search Form -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Project Search</h2>
                <p class="card-subtitle">Search for projects by name, customer, or description</p>
            </div>
            <div class="card-body">
                <form method="GET" action="">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="search_term" class="form-label">Search Query</label>
                            <input type="text" id="search_term" name="search_term" class="form-control" 
                                   value="<?php echo htmlspecialchars($search_term); ?>"
                                   placeholder="Enter project name, customer name, or description...">
                            <small class="form-text">Search across project names, customer names, and descriptions</small>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <span class="icon">🔍</span>
                            Search Projects
                        </button>
                        <?php if (!empty($search_term)): ?>
                            <a href="<?php echo BASE_URL; ?>Views/search_projects.php" class="btn btn-secondary">
                                <span class="icon">🔄</span>
                                Clear Search
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
        <!-- Search Results -->
        <?php if (!empty($search_term)): ?>
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Search Results</h2>
                    <p class="card-subtitle">
                        <?php if (empty($projects_result)): ?>
                            No projects found matching "<?php echo htmlspecialchars($search_term); ?>"
                        <?php else: ?>
                            Found <?php echo count($projects_result); ?> project(s) matching "<?php echo htmlspecialchars($search_term); ?>"
                        <?php endif; ?>
                    </p>
                </div>
                <div class="card-body">
                    <?php if (empty($projects_result)): ?>
                        <div class="notification notification-info">
                            <p><strong>No projects found.</strong></p>
                            <p>Try adjusting your search terms or browse all projects.</p>
                            <div class="mt-3">
                                <a href="<?php echo BASE_URL; ?>Views/projects/list_projects.php" class="btn btn-primary">
                                    <span class="icon">📋</span>
                                    View All Projects
                                </a>
                                <a href="<?php echo BASE_URL; ?>Views/projects/add_project.php" class="btn btn-ghost">
                                    <span class="icon">➕</span>
                                    Add New Project
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="grid grid-cols-1 gap-6">
                            <?php foreach ($projects_result as $project): ?>
                                <div class="card">
                                    <div class="card-body">
                                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                                            <!-- Project Info -->
                                            <div class="lg:col-span-2">
                                                <h3 class="card-title"><?php echo htmlspecialchars($project['project_name']); ?></h3>
                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                                                    <div class="info-item">
                                                        <label class="info-label">Customer:</label>
                                                        <span class="info-value"><?php echo htmlspecialchars($project['customer_name']); ?></span>
                                                    </div>
                                                    <div class="info-item">
                                                        <label class="info-label">Design Date:</label>
                                                        <span class="info-value"><?php echo htmlspecialchars($project['design_date']); ?></span>
                                                    </div>
                                                    <div class="info-item">
                                                        <label class="info-label">Due Date:</label>
                                                        <span class="info-value"><?php echo htmlspecialchars($project['due_date'] ?? 'Not set'); ?></span>
                                                    </div>
                                                    <div class="info-item">
                                                        <label class="info-label">Labor Hours:</label>
                                                        <span class="info-value"><?php echo htmlspecialchars($project['labor_hours']); ?> hours</span>
                                                    </div>
                                                    <div class="info-item">
                                                        <label class="info-label">Laser Time:</label>
                                                        <span class="info-value"><?php echo htmlspecialchars($project['laser_time']); ?> minutes</span>
                                                    </div>
                                                    <div class="info-item">
                                                        <label class="info-label">Router Time:</label>
                                                        <span class="info-value"><?php echo htmlspecialchars($project['router_time']); ?> minutes</span>
                                                    </div>
                                                </div>
                                                <?php if (!empty($project['project_description'])): ?>
                                                    <div class="mt-4">
                                                        <label class="info-label">Description:</label>
                                                        <p class="text-muted"><?php echo htmlspecialchars($project['project_description']); ?></p>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <!-- Project Actions -->
                                                <div class="btn-group mt-4">
                                                    <a href="<?php echo BASE_URL; ?>Views/projects/edit_project.php?project_id=<?php echo $project['id']; ?>" 
                                                       class="btn btn-primary btn-sm">
                                                        <span class="icon">✏️</span>
                                                        Edit Project
                                                    </a>
                                                    <a href="<?php echo BASE_URL; ?>Views/estimate/estimate.php?project_id=<?php echo $project['id']; ?>" 
                                                       class="btn btn-secondary btn-sm">
                                                        <span class="icon">💰</span>
                                                        Estimate
                                                    </a>
                                                    <button onclick="confirmDelete(<?php echo $project['id']; ?>)" 
                                                            class="btn btn-danger btn-sm">
                                                        <span class="icon">🗑️</span>
                                                        Delete
                                                    </button>
                                                </div>
                                            </div>

                                            <!-- Project Image -->
                                            <div class="text-center">
                                                <?php if (!empty($project['image_upload'])): ?>
                                                    <?php $image_url = BASE_URL . "projects/project_images/" . basename($project['image_upload']); ?>
                                                    <img src="<?php echo htmlspecialchars($image_url); ?>" 
                                                         alt="Project Image" 
                                                         class="image-thumbnail cursor-pointer"
                                                         onclick="openImage('<?php echo htmlspecialchars($image_url); ?>')">
                                                <?php else: ?>
                                                    <div class="no-image-placeholder">
                                                        <span class="icon">📷</span>
                                                        <p>No image available</p>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <script>
        function confirmDelete(projectId) {
            if (confirm('Are you sure you want to delete this project? This action cannot be undone.')) {
                window.location.href = '<?php echo BASE_URL; ?>public/projects/delete_project.php?project_id=' + projectId;
            }
        }

        function openImage(url) {
            const imgWindow = window.open("", "_blank", "width=800,height=600");
            imgWindow.document.write(`
                <html>
                <head>
                    <title>Project Image</title>
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
                    </style>
                </head>
                <body>
                    <button class="close-button" onclick="window.history.back()">Close</button>
                    <img src="${url}" alt="Project Image">
                </body>
                </html>
            `);
        }

        // Auto-focus the search input when page loads
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('search_term');
            if (searchInput && !searchInput.value) {
                searchInput.focus();
            }
        });
    </script>
</body>
</html>
<?php
$conn = null; // Close the connection
?>
