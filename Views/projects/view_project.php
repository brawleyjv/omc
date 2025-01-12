<?php
require_once __DIR__ . '/../../Globals/Config.php';
require_once __DIR__ . '/../../Models/Database.php';
require_once __DIR__ . '/../../Controllers/ProjectController.php';

use MyApp\Controllers\ProjectController;
use MyApp\Models\Database;
use Globals\Config;

$database = new Database(Config::DB_HOST, Config::DB_NAME, Config::DB_USER, Config::DB_PASS);
$controller = new ProjectController($database);

$search_term = isset($_GET['search_term']) ? $_GET['search_term'] : '';
$projects = [];

if (!empty($search_term)) {
    $projects = $controller->searchProjects($search_term);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Project</title>
    <link rel="stylesheet" href="../../public/css/styles.css">
    <style>
        .container {
            margin-top: 50px;
        }
        .title {
            text-align: center;
            margin-bottom: 20px;
        }
        .project-details {
            margin-bottom: 20px;
        }
        .project-image {
            max-width: 100%;
            height: auto;
        }
        .file-list {
            list-style-type: none;
            padding: 0;
        }
        .file-list li {
            margin-bottom: 10px;
        }
        .btn.styled-btn {
            padding: 10px 20px;
            background-color: #007BFF;
            color: white;
            border: none;
            cursor: pointer;
            text-decoration: none;
            text-align: center;
            border-radius: 5px;
            margin: 10px; /* Add margin to separate buttons */
        }
        .btn.styled-btn:hover {
            background-color: #0056b3;
        }
        .search-container {
            text-align: center;
            margin-bottom: 20px;
        }
        .search-container input[type="text"] {
            padding: 10px;
            width: 300px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .search-container input[type="submit"] {
            padding: 10px 20px;
            background-color: #007BFF;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 5px;
        }
        .search-container input[type="submit"]:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <?php include '../../Views/header.php'; ?>
    <div class="container">
        <div class="search-container">
            <form action="view_project.php" method="get">
                <input type="text" name="search_term" placeholder="Enter Project ID, Customer Name, or Project Name" required>
                <input type="submit" value="Search">
            </form>
        </div>
        <?php if (!empty($projects)): ?>
            <?php foreach ($projects as $project): ?>
                <h1 class="title"><?php echo htmlspecialchars($project['project_name']); ?></h1>
                <div class="project-details">
                    <?php if (!empty($project['image_upload'])): ?>
                        <?php
                        $image_upload = basename($project['image_upload']);
                        $image_upload_path = "http://localhost/OMC/projects/project_files/{$project['project_name']}/{$image_upload}";
                        ?>
                        <img src="<?php echo $image_upload_path; ?>" alt="Project Image" class="project-image">
                    <?php endif; ?>
                </div>
                <div class="file-list-container">
                    <h2>Files:</h2>
                    <ul class="file-list">
                        <?php
                        $file_uploads = explode(',', $project['file_upload']);
                        foreach ($file_uploads as $file_upload) {
                            $file_upload = basename($file_upload);
                            $file_upload_path = "http://localhost/OMC/projects/project_files/{$project['project_name']}/{$file_upload}";
                            echo "<li><a href='{$file_upload_path}' download>{$file_upload}</a></li>";
                        }
                        ?>
                    </ul>
                </div>
                <div class="file-list-container">
                    <h2>Design Files:</h2>
                    <ul class="file-list">
                        <?php
                        $design_files = explode(',', $project['design_file']);
                        foreach ($design_files as $design_file) {
                            $design_file = basename($design_file);
                            $design_file_path = "http://localhost/OMC/projects/project_files/{$project['project_name']}/{$design_file}";
                            echo "<li><a href='{$design_file_path}' download>{$design_file}</a></li>";
                        }
                        ?>
                    </ul>
                </div>
            <?php endforeach; ?>
        <?php elseif (!empty($search_term)): ?>
            <p>Project not found.</p>
        <?php endif; ?>
        <div class="button-container">
            <a href="ProjMain.php" class="btn styled-btn">Back to Main</a>
        </div>
    </div>
</body>
</html>
