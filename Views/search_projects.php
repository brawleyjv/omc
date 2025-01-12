<?php
require_once '../Globals/config.php'; // Include the configuration file for database connection

use Globals\Config;

try {
    $conn = new PDO("mysql:host=" . Config::DB_HOST . ";dbname=" . Config::DB_NAME, Config::DB_USER, Config::DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $search_term = isset($_GET['search_term']) ? $_GET['search_term'] : '';

    $projects_result = null;
    if (!empty($search_term)) {
        // Fetch projects based on search term
        $projects_sql = "SELECT * FROM projects WHERE project_name LIKE :search_term OR customer_name LIKE :search_term";
        $stmt = $conn->prepare($projects_sql);
        $stmt->execute(['search_term' => '%' . $search_term . '%']);
        $projects_result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Projects</title>
    <link rel="stylesheet" href="../public/css/styles.css"> <!-- Corrected the path to the CSS file in the root directory -->
    <style>
        .project {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            padding: 10px;
        }
        .project-info, .bom-list {
            flex: 1;
            margin: 10px;
        }
        .bom-list ul {
            list-style-type: none;
            padding: 0;
        }
        .buttons {
            display: flex;
            gap: 10px;
            flex-direction: column;
        }
        .button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            text-align: center;
        }
        .button:hover {
            background-color: #0056b3;
        }
        .delete-button {
            background-color: #dc3545;
        }
        .delete-button:hover {
            background-color: #c82333;
        }
        .thumbnail {
            max-width: 150px;
            height: auto;
            cursor: pointer;
        }
    </style>
    <script>
        function confirmDelete(projectId) {
            if (confirm('Are you sure you want to delete this project? This action cannot be undone.')) {
                window.location.href = 'delete_project.php?project_id=' + projectId;
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
    <?php include '../views/header.php'; ?> <!-- Corrected the path to the header file in the views directory -->
    <h1>Search Projects</h1>
    <form action="search_projects.php" method="get">
        <input type="text" name="search_term" placeholder="Search by project name or customer name" value="<?php echo htmlspecialchars($search_term); ?>">
        <input type="submit" value="Search">
    </form>
    <div class="project-list">
        <?php
        if ($projects_result && count($projects_result) > 0) {
            foreach ($projects_result as $project) {
                echo "<div class='project'>";
                echo "<div class='project-info'>";
                echo "<h2>" . htmlspecialchars($project['project_name']) . "</h2>";
                echo "<p><strong>Design Date:</strong> " . htmlspecialchars($project['design_date']) . "</p>";
                echo "<p><strong>Customer Name:</strong> " . htmlspecialchars($project['customer_name']) . "</p>";
                echo "<p><strong>Laser Time:</strong> " . htmlspecialchars($project['laser_time']) . " minutes</p>";
                echo "<p><strong>Router Time:</strong> " . htmlspecialchars($project['router_time']) . " minutes</p>";
                echo "<p><strong>Labor Hours:</strong> " . htmlspecialchars($project['labor_hours']) . " hours</p>";
                echo "<p><strong>Project Description:</strong> " . htmlspecialchars($project['project_description']) . "</p>";
                echo "<p><strong>Due Date:</strong> " . htmlspecialchars($project['due_date']) . "</p>";
                echo "<div class='buttons'>";
                echo "<a href='edit_project.php?project_id=" . $project['id'] . "' class='button'>Edit Project</a>";
                echo "<button class='button delete-button' onclick='confirmDelete(" . $project['id'] . ")'>Delete Project</button>";
                echo "<a href='estimate.php?project_id=" . $project['id'] . "' class='button'>Estimate</a>";
                echo "</div>";
                echo "</div>";

                if (!empty($project['image_upload'])) {
                    $image_url = "../projects/project_images/" . basename($project['image_upload']);
                    echo "<div class='project-thumbnail'>";
                    echo "<img src='" . htmlspecialchars($image_url) . "' alt='Project Image' class='thumbnail' onclick='openImage(\"" . htmlspecialchars($image_url) . "\")'>";
                    echo "</div>";
                }

                $project_id = $project['id'];
                $bom_sql = "SELECT description FROM bom WHERE project_id = :project_id";
                $stmt = $conn->prepare($bom_sql);
                $stmt->execute(['project_id' => $project_id]);
                $bom_result = $stmt->fetchAll(PDO::FETCH_ASSOC);

                echo "<div class='bom-list'>";
                if (count($bom_result) > 0) {
                    echo "<h3>Bill of Materials</h3>";
                    echo "<ul>";
                    foreach ($bom_result as $bom) {
                        echo "<li>" . htmlspecialchars($bom['description']) . "</li>";
                    }
                    echo "</ul>";
                    echo "<div class='buttons'>";
                    echo "<a href='edit_bom.php?project_id=" . $project_id . "' class='button'>Edit BOM</a>";
                    echo "<a href='BOM.php?project_id=" . $project_id . "' class='button'>View BOM</a>";
                    echo "</div>";
                } else {
                    echo "<p>No Bill of Materials added yet.</p>";
                    echo "<a href='Add_BOM.php?project_name=" . urlencode($project['project_name']) . "' class='button'>Add BOM</a>";
                }
                echo "</div>";

                echo "</div>";
            }
        } else {
            echo "<p>No projects found.</p>";
        }
        ?>
    </div>
</body>
</html>
<?php
$conn = null; // Close the connection
?>
