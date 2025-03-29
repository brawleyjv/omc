<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/OMC/config.php'; // Corrected path to config.php
require_once BASE_PATH . '/Models/Database.php';
require_once BASE_PATH . '/Controllers/ProjectController.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Project</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/styles.css"> <!-- Corrected CSS path -->
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
        .error-message {
            color: red;
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/OMC/Views/header.php'; ?> <!-- Corrected header path -->
    <div class="container">
        <h1 class="title">Add Project</h1>
        <div class="error-message">
            <?php if (!empty($_GET['error'])): ?>
                <?php echo htmlspecialchars($_GET['error']); ?>
            <?php endif; ?>
        </div>
        <form id="project-form" action="<?php echo BASE_URL; ?>public/projects/add_project.php" method="post" enctype="multipart/form-data" onsubmit="return validateForm()">
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
                    <input type="file" id="design_file" name="design_file[]" multiple>
                </div>
                <div class="due-date-group">
                    <label for="due_date">Project Due By Date:</label>
                    <input type="date" id="due_date" name="due_date">
                </div>
            </div>
            <div class="button-container">
                <button type="submit" class="btn styled-btn">Add Project</button>
                <button type="button" class="btn styled-btn red" onclick="window.location.href='<?php echo BASE_URL; ?>views/main.php'">Close</button>
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
