<?php
require_once __DIR__ . '/../../config.php'; // Updated to use __DIR__
require_once BASE_PATH . '/Models/Database.php'; // Updated to use BASE_PATH
require_once BASE_PATH . '/Controllers/ProjectController.php'; // Updated to use BASE_PATH

use MyApp\Models\Database;
use MyApp\Controllers\ProjectController;

// Ensure Database is instantiated with required arguments
$database = new Database(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
$db = $database->getConnection(); // Ensure $db is a PDO instance
$projectsController = new ProjectController($db); // Pass the PDO instance to the controller

$search_term = isset($_GET['search_term']) ? $_GET['search_term'] : '';

$results = [];
$noResults = false;
if (!empty($search_term)) {
    // Ensure searchProjects method exists in ProjectController
    $results = $projectsController->searchProjects($search_term);
    if (empty($results)) {
        $noResults = true;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ...existing code...
    header("Location: " . BASE_URL . "Views/projects/search_projects.php"); // Updated to use BASE_URL
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Projects</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>styles.css"> <!-- Corrected CSS path -->
    <style>
        .close-button {
            position: absolute;
            top: 10px;
            left: 10px;
        }
        .center-title {
            text-align: center;
            margin-top: 20px; /* Adjust margin to bring the title up */
        }
        .content {
            margin-top: 0px; /* Remove margin to bring the content up */
        }
        .button {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            background-color: #007BFF;
            color: white;
            text-decoration: none;
            font-size: 16px;
            cursor: pointer;
            display: inline-block;
            margin-top: 20px;
        }
        .button:hover {
            background-color: #0056b3;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 2px solid #007BFF; /* Enhance border appearance */
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        .thumbnail {
            max-width: 100px;
            max-height: 100px;
            cursor: pointer;
        }
    </style>
    <script>
        function validateSearchForm() {
            var searchTerm = document.getElementsByName('search_term')[0].value;
            if (searchTerm.trim() === '') {
                alert('Please enter a search term.');
                return false;
            }
            return true;
        }

        function clearResults() {
            document.getElementsByName('search_term')[0].value = '';
            document.getElementById('results').innerHTML = '';
        }

       

        window.onload = function() {
            if (<?php echo json_encode($noResults); ?>) {
                alert('No projects found.');
            }
        }

        function openImage(url) {
            const imgWindow = window.open("", "_blank", "width=800,height=600");
            imgWindow.document.write(`
                <html>
                <head>
                    <title>Image Viewer</title>
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
    </script>
</head>
<body>
    <?php include BASE_PATH . '/Views/header.php'; ?> <!-- Updated to use BASE_PATH -->
    <h1 class="center-title">Search Projects</h1>
    <form action="<?php echo BASE_URL; ?>Views/projects/search_projects.php" method="get" style="text-align: center;" onsubmit="return validateSearchForm()"> <!-- Updated to use BASE_URL -->
        <input type="text" name="search_term" placeholder="Search for projects" value="<?php echo htmlspecialchars($search_term); ?>">
        <div style="display: inline-block; margin-top: 20px;">
            <button type="submit" class="btn styled-btn" style="margin-right: 20px;">Search</button>
            <button type="button" class="btn styled-btn" onclick="window.location.href='<?php echo BASE_URL; ?>Views/projects/index.php'">Cancel</button> <!-- Updated to use BASE_URL -->
        </div>
    </form>
    <h1 class="center-title">Search Results</h1>
    <div class="content" id="results">
        <table>
            <thead>
                <tr>
                    <th>Project Name</th>
                    <th>Project Description</th>
                    <th>Design Date</th>
                    <th>Customer Name</th>
                    <th>Laser Time</th>
                    <th>Router Time</th>
                    <th>Labor Hours</th>
                    <th>Machine File</th>
                    <th>Project Image</th>
                    <th>Due Date</th>
                    <th>Actions</th> <!-- Add Actions column -->
                </tr>
            </thead>
            <tbody>
                <?php foreach ($results as $row): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['project_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['project_description']); ?></td>
                        <td><?php echo htmlspecialchars($row['design_date']); ?></td>
                        <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['laser_time']); ?></td>
                        <td><?php echo htmlspecialchars($row['router_time']); ?></td>
                        <td><?php echo htmlspecialchars($row['labor_hours']); ?></td>
                        <td>
                            <?php if (!empty($row['file_upload'])): ?>
                                <?php
                                $file_uploads = explode(',', $row['file_upload']);
                                foreach ($file_uploads as $file_upload) {
                                    $file_upload_label = pathinfo($file_upload, PATHINFO_FILENAME);
                                    $file_upload_path = BASE_URL . "projects/project_files/{$row['project_name']}/{$file_upload}"; // Updated to use BASE_URL
                                    echo "<a href='{$file_upload_path}' download>{$file_upload_label}</a><br>";
                                }
                                ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!empty($row['image_upload'])): ?>
                                <?php
                                $image_uploads = explode(',', $row['image_upload']);
                                $first_image_upload = $image_uploads[0];
                                $image_upload_paths = array_map(function($image) use ($row) {
                                    return BASE_URL . "projects/project_files/{$row['project_name']}/{$image}"; // Updated to use BASE_URL
                                }, $image_uploads);
                                echo "<a href='javascript:void(0);' onclick='openImage(\"{$image_upload_paths[0]}\")'><img src='{$image_upload_paths[0]}' alt='Project Image' class='thumbnail'></a>";
                                ?>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($row['due_date']); ?></td>
                        <td>
                            <!-- Add Edit and Delete buttons -->
                            <a href="<?php echo BASE_URL; ?>Views/projects/edit_project.php?project_name=<?php echo urlencode($row['project_name']); ?>" class="btn styled-btn white">Edit</a>
                            <form action="<?php echo BASE_URL; ?>public/projects/list_projects.php" method="post" onsubmit="return confirm('Are you sure you want to delete this project?');" style="display:inline;">
                                <input type="hidden" name="delete_project_name" value="<?php echo htmlspecialchars($row['project_name']); ?>">
                                <input type="submit" class="btn styled-btn red" value="Delete">
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <button class="btn styled-btn clear-button" onclick="clearResults()">Clear Results</button>
    <button class="btn styled-btn close-button" onclick="window.location.href='<?php echo BASE_URL; ?>Views/projects/index.php'">Close</button> <!-- Updated to use BASE_URL -->
</body>
</html>