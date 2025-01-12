<?php
require_once __DIR__ . '/../../Globals/Config.php';
require_once __DIR__ . '/../../Models/Database.php';
require_once __DIR__ . '/../../Controllers/ProjectController.php';

use MyApp\Controllers\ProjectController;
use MyApp\Models\Database;
use Globals\Config;

$database = new Database(Config::DB_HOST, Config::DB_NAME, Config::DB_USER, Config::DB_PASS);
$projectsController = new ProjectController($database);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_project_id'])) {
    $projectId = $_POST['delete_project_id'];
    error_log("Deleting project with ID: $projectId"); // Log the project ID being deleted
    $projectsController->deleteProject($projectId);
    header('Location: list_projects.php'); // Redirect to refresh the list after deletion
    exit;
}

$projects = $projectsController->listProjects(); // Use the correct method to list projects
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>List Projects</title>
    <link rel="stylesheet" href="../../public/css/styles.css">
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
                    <title>Image</title>
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
    <?php include '../../Views/header.php'; ?>
    <h1 class="center-title">List of Projects</h1>
    <div class="top-buttons">
        <button class="btn styled-btn" style="margin-right: 20px;" onclick="window.location.href='ProjMain.php'">Close</button>
    </div>
    <div class="content">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Project Name</th>
                    <th>Design Date</th>
                    <th>Customer Name</th>
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
                        <td><?php echo htmlspecialchars($project['id']); ?></td>
                        <td><?php echo htmlspecialchars($project['project_name']); ?></td>
                        <td><?php echo htmlspecialchars($project['design_date']); ?></td>
                        <td><?php echo htmlspecialchars($project['customer_name']); ?></td>
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
                                    $file_upload_path = "http://localhost/OMC/projects/project_files/{$project['project_name']}/{$file_upload}";
                                    echo "<a href='{$file_upload_path}' download>{$file_upload_label}</a><br>";
                                }
                                ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!empty($project['image_upload'])): ?>
                                <?php
                                $image_upload = basename($project['image_upload']);
                                $image_upload_path = "http://localhost/OMC/projects/project_files/{$project['project_name']}/{$image_upload}";
                                echo "<img src='{$image_upload_path}' alt='Project Image' class='thumbnail' onclick='openImage(\"{$image_upload_path}\")'>";
                                ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!empty($project['design_file'])): ?>
                                <?php
                                $design_file = basename($project['design_file']);
                                $design_file_path = "http://localhost/OMC/projects/project_files/{$project['project_name']}/{$design_file}";
                                echo "<a href='{$design_file_path}' download>{$design_file}</a>";
                                ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="edit_projects.php?project_id=<?php echo $project['id']; ?>" class="btn styled-btn white">Edit</a>
                            <form action="list_projects.php" method="post" onsubmit="return confirm('Are you sure you want to delete this project?');" style="display:inline;">
                                <input type="hidden" name="delete_project_id" value="<?php echo htmlspecialchars($project['id']); ?>">
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