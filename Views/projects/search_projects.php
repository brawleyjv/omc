<?php
// filepath: /c:/xampp/htdocs/OMC/public/projects/search_projects.php

require_once __DIR__ . '/../../Globals/Config.php';
require_once __DIR__ . '/../../Models/Database.php';
require_once __DIR__ . '/../../Models/Project.php';
require_once __DIR__ . '/../../Controllers/ProjectController.php';
use MyApp\Models\Database;
use MyApp\Controllers\ProjectController;

// Ensure Database is instantiated with required arguments
$database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS);
$projectsController = new ProjectController($database);

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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Projects</title>
    <link rel="stylesheet" href="../../public/css/styles.css">
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

        function promptEdit() {
            var projectId = prompt('Enter the ID of the project you want to edit:');
            if (projectId) {
                window.location.href = '../../Views/projects/edit_projects.php?project_id=' + projectId;
            }
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
    <h1 class="center-title">Search Projects</h1>
    <form action="search_projects.php" method="get" style="text-align: center;" onsubmit="return validateSearchForm()">
        <input type="text" name="search_term" placeholder="Search for projects" value="<?php echo htmlspecialchars($search_term); ?>">
        <div style="display: inline-block; margin-top: 20px;">
            <button type="submit" class="btn styled-btn" style="margin-right: 20px;">Search</button>
            <button type="button" class="btn styled-btn" onclick="promptEdit()" style="margin-right: 20px;">Edit Project</button>
            <button type="button" class="btn styled-btn" onclick="window.location.href='projmain.php'">Cancel</button>
        </div>
    </form>
    <h1 class="center-title">Search Results</h1>
    <div class="content" id="results">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
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
                </tr>
            </thead>
            <tbody>
                <?php foreach ($results as $row): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['id']); ?></td>
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
                                    $file_upload_path = "http://localhost/OMC/projects/project_files/{$row['project_name']}/{$file_upload}";
                                    echo "<a href='{$file_upload_path}' download>{$file_upload_label}</a><br>";
                                }
                                ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!empty($row['image_upload'])): ?>
                                <?php
                                $image_upload = basename($row['image_upload']);
                                $image_upload_path = "http://localhost/OMC/projects/project_files/{$row['project_name']}/{$image_upload}";
                                echo "<img src='{$image_upload_path}' alt='Project Image' class='thumbnail' onclick='openImage(\"{$image_upload_path}\")'>";
                                ?>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($row['due_date']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <button class="btn styled-btn clear-button" onclick="clearResults()">Clear Results</button>
    <button class="btn styled-btn close-button" onclick="window.location.href='projmain.php'">Close</button>
</body>
</html>