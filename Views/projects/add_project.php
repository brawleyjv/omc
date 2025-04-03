<?php
require_once realpath(dirname(__FILE__) . '/../../config.php');
require_once BASE_PATH . '/Models/Database.php';
require_once BASE_PATH . '/Controllers/CustomerController.php';
require_once BASE_PATH . '/Controllers/ProjectController.php';

use MyApp\Models\Database;
use MyApp\Controllers\CustomerController;
use MyApp\Controllers\ProjectController;

$database = new Database(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
$conn = $database->getConnection();
$customerController = new CustomerController($conn);
$projectController = new ProjectController($conn);

$customer_id = $_GET['customer_id'] ?? ''; // Capture the customer_id from the query string
$customer_name = $_GET['customer_name'] ?? '';
$project_name = $_GET['project_name'] ?? ''; // Capture the project name from the query string

if (empty($customer_name) && empty($customer_id)) {
    // Ask for the customer name only if both customer_name and customer_id are missing
    echo "<script>
        var customerName = prompt('Enter the customer name:');
        if (customerName) {
            window.location.href = '?customer_name=' + encodeURIComponent(customerName);
        } else {
            window.location.href = '" . BASE_URL . "Views/projects/index.php';
        }
    </script>";
    exit;
}

if (!empty($customer_name)) {
    $customer = $customerController->viewCustomerByName($customer_name);

    if (!$customer) {
        // Redirect to add_customer.php if the customer doesn't exist
        header("Location: " . BASE_URL . "Views/customers/add_customer.php?customer_name=" . urlencode($customer_name) . "&project_name=" . urlencode($project_name) . "&redirect_to=" . urlencode(BASE_URL . "Views/projects/add_project.php"));
        exit;
    }

    $customer_id = $customer['customer_id']; // Set the customer_id from the database
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $projectName = trim($_POST['project_name']);
    $designDate = trim($_POST['design_date']);
    $laserTime = intval($_POST['laser_time'] ?? 0);
    $routerTime = intval($_POST['router_time'] ?? 0);
    $laborHours = intval($_POST['labor_hours'] ?? 0);
    $projectDescription = trim($_POST['project_description']);
    $dueDate = trim($_POST['due_date']);
    $fileUpload = implode(',', $_FILES['file_upload']['name'] ?? []);
    $imageUpload = implode(',', $_FILES['image_upload']['name'] ?? []);
    $designFile = implode(',', $_FILES['design_file']['name'] ?? []);
    $customerId = intval($_POST['customer_id']); // Ensure customer_id is passed

    if (empty($customerId) || empty($projectName)) {
        header("Location: " . BASE_URL . "Views/projects/add_project.php?error=Customer ID and Project Name are required");
        exit();
    }

    try {
        $projectId = $projectController->addProject(
            $projectName,
            $designDate,
            $customer_name,
            $laserTime,
            $routerTime,
            $laborHours,
            $projectDescription,
            $dueDate,
            $fileUpload,
            $imageUpload,
            $designFile,
            $customerId // Pass customer_id
        );

        // Redirect to list_projects.php with customer_id in the query string
        header("Location: " . BASE_URL . "Views/projects/list_projects.php?customer_id=" . urlencode($customerId) . "&success=Project added successfully");
    } catch (Exception $e) {
        error_log("Failed to add project: " . $e->getMessage());
        header("Location: " . BASE_URL . "Views/projects/add_project.php?error=Failed to add project");
    }
    exit();
}

// Continue loading the add_project form
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Project</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>styles.css"> <!-- Corrected CSS path -->
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
    <?php include realpath(dirname(__FILE__) . '/../../Views/header.php'); ?> <!-- Updated to use realpath -->
    <div class="container">
        <h1 class="title">Add Project</h1>
        <div class="error-message">
            <?php if (!empty($_GET['error'])): ?>
                <?php echo htmlspecialchars($_GET['error']); ?>
            <?php endif; ?>
        </div>
        <form id="project-form" action="" method="post" enctype="multipart/form-data" onsubmit="return validateForm()">
            <input type="hidden" name="customer_id" value="<?php echo htmlspecialchars($customer_id); ?>"> <!-- Prefill customer_id -->
            <div class="form-container">
                <div class="form-group">
                    <label for="project_name">Project Name:</label>
                    <input type="text" id="project_name" name="project_name" value="<?php echo htmlspecialchars($project_name); ?>" required>
                </div>
                <div class="form-group">
                    <label for="design_date">Design Date:</label>
                    <input type="date" id="design_date" name="design_date" required>
                </div>
                <div class="form-group">
                    <label for="customer_name">Customer Name:</label>
                    <input type="text" id="customer_name" name="customer_name" value="<?php echo htmlspecialchars($customer_name); ?>" readonly>
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
                <div class="form-group">
                    <label for="customers">Assign Customers:</label>
                    <select name="customers[]" id="customers" multiple>
                        <?php foreach ($customers as $customer): ?>
                            <option value="<?php echo $customer['customer_id']; ?>"><?php echo $customer['name']; ?></option>
                        <?php endforeach; ?>
                    </select>
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
