<?php
require_once __DIR__ . '/../../Views/projects/edit_projects.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Project</title>
    <link rel="stylesheet" href="../../public/css/styles.css">
    <style>
        .title {
            text-align: center;
            margin-top: 200px; /* Lower the title by 200px */
        }
        .form-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            margin-top: 20px; /* Add margin to separate form from title */
        }
        .form-group {
            flex: 1 1 30%; /* Adjust the percentage to control the width of each column */
            margin: 5px; /* Reduced margin for less spacing */
        }
        .form-group label, .form-group input, .form-group textarea {
            display: block;
            width: 100%;
        }
        .form-group input[type="date"],
        .form-group input[type="number"] {
            width: auto; /* Adjust the width to fit the content */
        }
        .file-group {
            display: flex;
            flex-direction: column;
            flex: 1 1 30%;
            margin: 5px;
        }
        .submit-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 5px;
            width: 100%;
        }
    </style>
    <script>
        function validateForm() {
            const requiredFields = [
                'project_name',
                'design_date',
                'customer_name',
                'laser_time',
                'router_time',
                'labor_hours',
                'project_description',
                'due_date'
            ];

            let isEmpty = false;
            requiredFields.forEach(field => {
                const value = document.getElementById(field).value.trim();
                if (value === '') {
                    isEmpty = true;
                }
            });

            const fileUpload = document.getElementById('file_upload').value.trim();
            const imageUpload = document.getElementById('image_upload').value.trim();

            if (isEmpty || fileUpload === '' || imageUpload === '') {
                const confirmLeaveBlank = confirm('Some fields are empty or file/image upload is blank. Do you want to proceed?');
                if (!confirmLeaveBlank) {
                    return false;
                } else {
                    // Remove empty fields from the form data
                    requiredFields.forEach(field => {
                        const element = document.getElementById(field);
                        if (element.value.trim() === '') {
                            element.disabled = true;
                        }
                    });

                    if (fileUpload === '') {
                        document.getElementById('file_upload').disabled = true;
                    }
                    if (imageUpload === '') {
                        document.getElementById('image_upload').disabled = true;
                    }
                }
            }

            return true;
        }
    </script>
</head>
<body>
    <?php include '../../Views/header.php'; ?>

    <?php if ($project): ?>
        <h1 class="title" style="margin-top: 200px;">Edit Project</h1> <!-- Add margin to bring the title down -->
        <form id="project-form" action="../../public/projects/edit_projects.php" method="post" enctype="multipart/form-data" onsubmit="return validateForm()"> <!-- Update form action -->
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($project['id']); ?>">
            <div class="submit-container" style="justify-content: space-between; width: 100%;">
                <button type="button" class="btn styled-btn" onclick="window.location.href='../../views/projects/list_projects.php'" style="margin-left: 0;">Cancel</button> <!-- Update cancel button -->
                <input type="submit" class="btn styled-btn" value="Update" style="margin-right: 0;">
            </div>
            <div class="form-container">
                <div class="form-group">
                    <label for="project_name">Project Name:</label>
                    <input type="text" id="project_name" name="project_name" value="<?php echo htmlspecialchars($project['project_name']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="design_date">Design Date:</label>
                    <input type="date" id="design_date" name="design_date" value="<?php echo htmlspecialchars($project['design_date']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="customer_name">Customer Name:</label>
                    <input type="text" id="customer_name" name="customer_name" value="<?php echo htmlspecialchars($project['customer_name']); ?>">
                </div>
                <div class="form-group">
                    <label for="laser_time">Laser Time (minutes):</label>
                    <input type="number" id="laser_time" name="laser_time" value="<?php echo htmlspecialchars($project['laser_time']); ?>" max="9999">
                </div>
                <div class="form-group">
                    <label for="router_time">Router Time (minutes):</label>
                    <input type="number" id="router_time" name="router_time" value="<?php echo htmlspecialchars($project['router_time']); ?>" max="9999">
                </div>
                <div class="form-group">
                    <label for="labor_hours">Labor Hours:</label>
                    <input type="number" id="labor_hours" name="labor_hours" value="<?php echo htmlspecialchars($project['labor_hours']); ?>" max="9999">
                </div>
                <div class="form-group">
                    <label for="project_description">Project Description:</label>
                    <textarea id="project_description" name="project_description" rows="10"><?php echo htmlspecialchars($project['project_description']); ?></textarea>
                </div>
                <div class="file-group">
                    <label for="file_upload">File Upload:</label>
                    <input type="file" id="file_upload" name="file_upload">
                    <?php if (!empty($project['file_upload'])): ?>
                        <p>Current file: <?php echo htmlspecialchars($project['file_upload']); ?></p>
                    <?php endif; ?>
                    <label for="image_upload">Image Upload:</label>
                    <input type="file" id="image_upload" name="image_upload" accept="image/*">
                    <?php if (!empty($project['image_upload'])): ?>
                        <p>Current image: <?php echo htmlspecialchars($project['image_upload']); ?></p>
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <label for="due_date">Project Due By Date:</label>
                    <input type="date" id="due_date" name="due_date" value="<?php echo htmlspecialchars($project['due_date']); ?>">
                </div>
            </div>
        </form>
    <?php else: ?>
        <p>Project not found.</p>
    <?php endif; ?>
</body>
</html>
