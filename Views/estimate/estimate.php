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
    <title>Estimate</title>
    <link rel="stylesheet" href="../../public/css/styles.css">
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
            gap: 20px; /* Add space between form groups */
        }
        .form-group {
            flex: 1 1 45%; /* Adjust the percentage to control the width of each column */
            margin: 10px 0; /* Add vertical margin for better spacing */
        }
        .form-group label, .form-group input, .form-group textarea {
            display: block;
            width: 100%;
        }
        .form-group input[type="date"],
        .form-group input[type="number"] {
            width: 100%; /* Ensure the input fields take full width */
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
        .project-list {
            margin-top: 20px;
        }
        .project-list ul {
            list-style-type: none;
            padding: 0;
        }
        .project-list li {
            margin-bottom: 10px;
        }
        .project-list a {
            text-decoration: none;
            color: #007BFF;
        }
        .project-list a:hover {
            text-decoration: underline;
        }
    </style>
    <script>
        function printPage() {
            window.print();
        }
    </script>
</head>
<body>
    <?php include '../../Views/header.php'; ?>
    <div class="container">
        <h1 class="title">Estimate</h1>
        <div class="search-container">
            <form action="estimate.php" method="get">
                <input type="text" name="search_term" placeholder="Enter Project ID, Customer Name, or Project Name" required>
                <input type="submit" value="Search">
            </form>
        </div>
        <?php if (!empty($projects)): ?>
            <div class="project-list">
                <ul>
                    <?php foreach ($projects as $project): ?>
                        <li>
                            <a href="estimate.php?project_id=<?php echo $project['id']; ?>">
                                <?php echo htmlspecialchars($project['project_name']); ?> (<?php echo htmlspecialchars($project['customer_name']); ?>)
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php elseif (!empty($search_term)): ?>
            <p>No projects found.</p>
        <?php endif; ?>
        <?php if (isset($_GET['project_id'])): ?>
            <?php
            $project_id = $_GET['project_id'];
            $project = $controller->getProjectById($project_id);
            if ($project):
            ?>
                <div class="project-details">
                    <h2>Project Details</h2>
                    <p><strong>Project Name:</strong> <?php echo htmlspecialchars($project['project_name']); ?></p>
                    <p><strong>Customer Name:</strong> <?php echo htmlspecialchars($project['customer_name']); ?></p>
                    <p><strong>Design Date:</strong> <?php echo htmlspecialchars($project['design_date']); ?></p>
                    <p><strong>Laser Time:</strong> <?php echo htmlspecialchars($project['laser_time']); ?> minutes</p>
                    <p><strong>Router Time:</strong> <?php echo htmlspecialchars($project['router_time']); ?> minutes</p>
                    <p><strong>Labor Hours:</strong> <?php echo htmlspecialchars($project['labor_hours']); ?> hours</p>
                    <p><strong>Project Description:</strong> <?php echo htmlspecialchars($project['project_description']); ?></p>
                    <p><strong>Due Date:</strong> <?php echo htmlspecialchars($project['due_date']); ?></p>
                    <p><strong>Machine File:</strong> <?php echo htmlspecialchars($project['file_upload']); ?></p>
                    <p><strong>Project Image:</strong> <?php echo htmlspecialchars($project['image_upload']); ?></p>
                    <p><strong>Design File:</strong> <?php echo htmlspecialchars($project['design_file']); ?></p>
                </div>
            <?php else: ?>
                <p>Project not found.</p>
            <?php endif; ?>
        <?php endif; ?>
        <div class="submit-container">
            <a href="../../views/projects/index.php" class="btn styled-btn">Cancel</a>
            <button type="button" class="btn styled-btn" onclick="printPage()">Print</button>
        </div>
    </div>
</body>
</html>
