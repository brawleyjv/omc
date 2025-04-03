<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once realpath(dirname(__FILE__) . '/../../config.php'); // Updated to use realpath(dirname(__FILE__))
require_once BASE_PATH . '/Models/Database.php'; // Updated to use BASE_PATH
require_once BASE_PATH . '/Controllers/ProjectController.php'; // Updated to use BASE_PATH

use MyApp\Controllers\ProjectController;
use MyApp\Models\Database;

$database = new Database();
$db = $database->getConnection(); // Ensure $db is a PDO instance
$projectsController = new ProjectController($db); // Pass the PDO instance to the controller

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_project_name'])) {
    $projectName = $_POST['delete_project_name'];
    error_log("Deleting project with name: $projectName"); // Log the project name being deleted
    $projectsController->deleteProjectByName($projectName);
    header("Location: " . BASE_URL . "Views/projects/list_projects.php"); // Updated to use BASE_URL
    exit;
}

$customerId = $_GET['customer_id'] ?? null;

if ($customerId) {
    $projects = $projectsController->getProjectsByCustomerId($customerId); // Fetch projects for the specific customer
} else {
    $projects = $projectsController->listProjects(); // Fetch all projects
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>List Projects</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>styles.css"> <!-- Corrected CSS path -->
    <style>
        .top-buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 0px; /* Remove margin to bring the buttons up */
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
        .btn.styled-btn.red {
            background-color: #DC3545; /* Red background */
            color: white; /* White text */
            padding: 5px 10px; /* Reduce padding */
            font-size: 14px; /* Reduce font size */
            border: none; /* Remove border */
        }
        .btn.styled-btn.red:hover {
            background-color: #c82333; /* Darker red on hover */
        }
        .btn.styled-btn {
            padding: 5px 10px; /* Reduce padding */
            font-size: 14px; /* Reduce font size */
            border: none; /* Remove border */
        }
        .btn.styled-btn.white {
            background-color: white; /* White background */
            color: #007BFF; /* Blue text */
            padding: 5px 10px; /* Reduce padding */
            font-size: 14px; /* Reduce font size */
            border: 2px solid #007BFF; /* Blue border */
        }
        .btn.styled-btn.white:hover {
            background-color: #f2f2f2; /* Light gray on hover */
        }
    </style>
    <script>
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
                    <button class="close-button" onclick="window.close()">Close</button>
                    <img src="${url}" alt="Project Image">
                </body>
                </html>
            `);
        }
    </script>
</head>
<body>
    <?php include BASE_PATH . '/Views/header.php'; ?> <!-- Updated to use BASE_PATH -->
    <h1 class="center-title">List of Projects</h1>
    <div class="top-buttons">
        <button class="btn styled-btn" style="margin-right: 20px;" onclick="window.location.href='<?php echo BASE_URL; ?>Views/main.php'">Close</button> <!-- Updated to use BASE_URL -->
    </div>
    <div class="content">
        <table>
            <thead>
                <tr>
                    <th>Project Name</th>
                    <th>Customer Name</th> <!-- Corrected label -->
                    <th>Design Date</th> <!-- Corrected label -->
                    <th>Laser Time</th>
                    <th>Router Time</th>
                    <th>Labor Hours</th>
                    <th>Project Description</th>
                    <th>Due Date</th>
                    <th>Machine File</th>
                    <th>Project Image</th>
                    <th>Design File</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($projects as $project): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($project['project_name'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($project['customer_name'] ?? 'Unknown'); // Corrected data ?></td>
                        <td><?php echo htmlspecialchars($project['design_date']); // Corrected data ?></td>
                        <td><?php echo htmlspecialchars($project['laser_time']); ?></td>
                        <td><?php echo htmlspecialchars($project['router_time']); ?></td>
                        <td><?php echo htmlspecialchars($project['labor_hours']); ?></td>
                        <td><?php echo htmlspecialchars($project['project_description']); ?></td>
                        <td><?php echo htmlspecialchars($project['due_date']); ?></td>
                        <td>
                            <?php if (!empty($project['file_upload'])): ?>
                                <?php
                                $file_uploads = explode(',', $project['file_upload']);
                                foreach ($file_uploads as $file_upload) {
                                    $file_upload_label = pathinfo($file_upload, PATHINFO_FILENAME);
                                    $file_upload_path = BASE_URL . "projects/project_files/{$project['project_name']}/{$file_upload}"; // Updated to use BASE_URL
                                    echo "<a href='{$file_upload_path}' download>{$file_upload_label}</a><br>";
                                }
                                ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!empty($project['image_upload'])): ?>
                                <?php
                                $image_uploads = explode(',', $project['image_upload']);
                                $first_image_upload = $image_uploads[0];
                                $image_upload_paths = array_map(function($image) use ($project) {
                                    return BASE_URL . "projects/project_files/{$project['project_name']}/{$image}"; // Updated to use BASE_URL
                                }, $image_uploads);
                                echo "<a href='javascript:void(0);' onclick='openImage(\"{$image_upload_paths[0]}\")'><img src='{$image_upload_paths[0]}' alt='Project Image' class='thumbnail'></a>";
                                ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!empty($project['design_file'])): ?>
                                <?php
                                $design_files = explode(',', $project['design_file']);
                                foreach ($design_files as $design_file) {
                                    $design_file_label = pathinfo($design_file, PATHINFO_FILENAME);
                                    $design_file_path = BASE_URL . "projects/project_files/{$project['project_name']}/{$design_file}"; // Updated to use BASE_URL
                                    echo "<a href='{$design_file_path}' download>{$design_file_label}</a><br>";
                                }
                                ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="<?php echo BASE_URL; ?>Views/projects/edit_project.php?project_name=<?php echo urlencode($project['project_name']); ?>" class="btn styled-btn white">Edit</a> <!-- Updated to use BASE_URL -->
                            <form action="<?php echo BASE_URL; ?>public/projects/list_projects.php" method="post" onsubmit="return confirm('Are you sure you want to delete this project?');" style="display:inline;"> <!-- Updated to use BASE_URL -->
                                <input type="hidden" name="delete_project_name" value="<?php echo htmlspecialchars($project['project_name']); ?>">
                                <input type="submit" class="btn styled-btn red" value="Delete">
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>