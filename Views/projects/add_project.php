<?php
require_once __DIR__ . '/../../Globals/Config.php';
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
        <form id="project-form" action="../../public/projects/submit_project.php" method="post" enctype="multipart/form-data">
            <div class="form-container">
                <div class="form-group">
                    <label for="project_name">Project Name:</label>
                    <input type="text" id="project_name" name="project_name" required>
                </div>
                <div class="form-group">
                    <label for="design_date">Design Date:</label>
                    <input type="date" id="design_date" name="design_date" required>
                </div>
                <div class="form-group">
                    <label for="customer_name">Customer Name:</label>
                    <input type="text" id="customer_name" name="customer_name">
                </div>
                <div class="form-group">
                    <label for="laser_time">Laser Time (minutes):</label>
                    <input type="number" id="laser_time" name="laser_time" max="9999">
                </div>
                <div class="form-group">
                    <label for="router_time">Router Time (minutes):</label>
                    <input type="number" id="router_time" name="router_time" max="9999">
                </div>
                <div class="form-group">
                    <label for="labor_hours">Labor Hours:</label>
                    <input type="number" id="labor_hours" name="labor_hours" max="9999">
                </div>
                <div class="form-group">
                    <label for="project_description">Project Description:</label>
                    <textarea id="project_description" name="project_description" rows="10"></textarea>
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
                    <input type="file" id="design_file" name="design_file">
                </div>
                <div class="due-date-group">
                    <label for="due_date">Project Due By Date:</label>
                    <input type="date" id="due_date" name="due_date">
                </div>
            </div>
        </form>
    </div>
</body>
</html>
