<?php
require_once __DIR__ . '/../../Globals/Config.php';
require_once __DIR__ . '/../../Models/Database.php';
require_once __DIR__ . '/../../Controllers/ProjectController.php';

use MyApp\Controllers\ProjectController;
use MyApp\Models\Database;
use Globals\Config;

$database = new Database(Config::DB_HOST, Config::DB_NAME, Config::DB_USER, Config::DB_PASS);
$controller = new ProjectController($database);

$project_name = '';
$design_date = '';
$customer_name = '';
$laser_time = '';
$router_time = '';
$labor_hours = '';
$project_description = '';
$due_date = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $project_name = $_POST['project_name'];
    $design_date = $_POST['design_date'];
    $customer_name = $_POST['customer_name'];
    $laser_time = $_POST['laser_time'];
    $router_time = $_POST['router_time'];
    $labor_hours = $_POST['labor_hours'];
    $project_description = $_POST['project_description'];
    $due_date = $_POST['due_date'];

    // Handle file uploads
    $file_uploads = !empty($_FILES['file_upload']['name'][0]) ? $_FILES['file_upload']['name'] : [];
    $image_uploads = !empty($_FILES['image_upload']['name'][0]) ? $_FILES['image_upload']['name'] : [];
    $design_files = !empty($_FILES['design_file']['name'][0]) ? $_FILES['design_file']['name'] : [];

    $upload_dir = 'C:/xampp/htdocs/OMC/projects/project_files/' . $project_name . '/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    if (!empty($_FILES['file_upload']['name'][0])) {
        $file_upload_paths = [];
        foreach ($_FILES['file_upload']['name'] as $key => $name) {
            $tmp_name = $_FILES['file_upload']['tmp_name'][$key];
            $file_upload_path = $upload_dir . $name;
            if (move_uploaded_file($tmp_name, $file_upload_path)) {
                $file_upload_paths[] = basename($file_upload_path);
            } else {
                echo 'Failed to upload file: ', $name;
                return;
            }
        }
        $file_uploads = implode(',', $file_upload_paths);
    }

    if (!empty($_FILES['image_upload']['name'][0])) {
        $image_upload_paths = [];
        foreach ($_FILES['image_upload']['name'] as $key => $name) {
            $tmp_name = $_FILES['image_upload']['tmp_name'][$key];
            $image_upload_path = $upload_dir . $name;
            if (move_uploaded_file($tmp_name, $image_upload_path)) {
                $image_upload_paths[] = basename($image_upload_path);
            } else {
                echo 'Failed to upload image: ', $name;
                return;
            }
        }
        $image_uploads = implode(',', $image_upload_paths);
    }

    if (!empty($_FILES['design_file']['name'][0])) {
        $design_file_paths = [];
        foreach ($_FILES['design_file']['name'] as $key => $name) {
            $tmp_name = $_FILES['design_file']['tmp_name'][$key];
            $design_file_path = $upload_dir . $name;
            if (move_uploaded_file($tmp_name, $design_file_path)) {
                $design_file_paths[] = basename($design_file_path);
            } else {
                echo 'Failed to upload design file: ', $name;
                return;
            }
        }
        $design_files = implode(',', $design_file_paths);
    }

    try {
        $controller->addProject(
            $project_name,
            $design_date,
            $customer_name,
            $laser_time,
            $router_time,
            $labor_hours,
            $project_description,
            $due_date,
            $file_uploads,
            $image_uploads,
            $design_files
        );
        header('Location: ../../Views/projects/list_projects.php');
        exit;
    } catch (Exception $e) {
        echo 'Failed to add project: ',  $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Project</title>
    <link rel="stylesheet" href="../../css/styles.css">
    <style>
        .title {
            text-align: center;
            margin-top: 50px; /* Adjust margin to bring the title up */
        }
        .form-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            margin-top: 20px; /* Adjust margin to bring the form up */
            gap: 20px; /* Reduce space between form groups */
        }
        .form-group {
            flex: 1 1 45%; /* Adjust the percentage to control the width of each column */
            margin: 5px 0; /* Reduce vertical margin for better spacing */
        }
        .form-group label, .form-group input, .form-group textarea {
            display: block;
            width: 100%;
        }
        .form-group input[type="date"],
        .form-group input[type="number"] {
            width: 100%; /* Ensure the input fields take full width */
        }
        .file-group {
            flex: 1 1 100%; /* Make file upload fields take full width */
            margin: 5px 0; /* Reduce vertical margin for better spacing */
        }
        .submit-container {
            display: flex;
            justify-content: center; /* Center the buttons */
            align-items: center;
            margin: 20px 0; /* Add vertical margin for better spacing */
            gap: 20px; /* Add space between buttons */
            padding: 20px; /* Add padding */
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
        }
        .btn.styled-btn:hover {
            background-color: #0056b3;
        }
        .due-date-group {
            flex: 1 1 45%; /* Adjust the percentage to control the width of each column */
            margin: 5px 0; /* Reduce vertical margin for better spacing */
        }
        .due-date-group input[type="date"] {
            width: auto; /* Reduce the width to only use the needed space */
        }
    </style>
</head>
<body>
    <?php include '../../Views/header.php'; ?>
    <div class="container">
        <h1 class="title">Add Project</h1>
        <div class="submit-container">
            <a href="../../views/projects/ProjMain.php" class="btn styled-btn">Cancel</a>
            <input type="submit" form="project-form" value="Submit" class="btn styled-btn" id="submit-button">
        </div>
        <form id="project-form" action="../../Views/projects/add_project.php" method="post" enctype="multipart/form-data" onsubmit="return validateForm()">
            <div class="form-container">
                <div class="form-group">
                    <label for="project_name">Project Name:</label>
                    <input type="text" id="project_name" name="project_name" value="<?php echo htmlspecialchars($project_name); ?>" required>
                </div>
                <div class="form-group">
                    <label for="design_date">Design Date:</label>
                    <input type="date" id="design_date" name="design_date" value="<?php echo htmlspecialchars($design_date); ?>" required>
                </div>
                <div class="form-group">
                    <label for="customer_name">Customer Name:</label>
                    <input type="text" id="customer_name" name="customer_name" value="<?php echo htmlspecialchars($customer_name); ?>">
                </div>
                <div class="form-group">
                    <label for="laser_time">Laser Time (minutes):</label>
                    <input type="number" id="laser_time" name="laser_time" value="<?php echo htmlspecialchars($laser_time); ?>" max="9999">
                </div>
                <div class="form-group">
                    <label for="router_time">Router Time (minutes):</label>
                    <input type="number" id="router_time" name="router_time" value="<?php echo htmlspecialchars($router_time); ?>" max="9999">
                </div>
                <div class="form-group">
                    <label for="labor_hours">Labor Hours:</label>
                    <input type="number" id="labor_hours" name="labor_hours" value="<?php echo htmlspecialchars($labor_hours); ?>" max="9999">
                </div>
                <div class="form-group">
                    <label for="project_description">Project Description:</label>
                    <textarea id="project_description" name="project_description" rows="10"><?php echo htmlspecialchars($project_description); ?></textarea>
                </div>
                <div class="file-group">
                    <label for="file_upload">File Upload:</label>
                    <input type="file" id="file_upload" name="file_upload[]" multiple>
                </div>
                <div class="file-group">
                    <label for="image_upload">Image Upload:</label>
                    <input type="file" id="image_upload" name="image_upload[]" accept=".bmp,.jpg,.jpeg,.tiff,.gif,.png" multiple>
                </div>
                <div class="file-group">
                    <label for="design_file">Design File:</label>
                    <input type="file" id="design_file" name="design_file[]" multiple>
                </div>
                <div class="due-date-group">
                    <label for="due_date">Project Due By Date:</label>
                    <input type="date" id="due_date" name="due_date" value="<?php echo htmlspecialchars($due_date); ?>">
                </div>
            </div>
        </form>
    </div>
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
