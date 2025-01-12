<?php
require_once __DIR__ . '/../../Globals/Config.php';
require_once __DIR__ . '/../../Models/Database.php';
require_once __DIR__ . '/../../Controllers/ProjectController.php';

use MyApp\Controllers\ProjectController;
use MyApp\Models\Database;
use Globals\Config;

$database = new Database(Config::DB_HOST, Config::DB_NAME, Config::DB_USER, Config::DB_PASS);
$controller = new ProjectController($database);

$project_id = isset($_GET['project_id']) ? $_GET['project_id'] : 0;
$project = $controller->getProjectById($project_id);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle file uploads
    $project_name = $_POST['project_name'];
    $file_uploads = !empty($_FILES['file_upload']['name'][0]) ? $_FILES['file_upload']['name'] : explode(',', $project['file_upload']);
    $image_upload = !empty($_FILES['image_upload']['name']) ? $_FILES['image_upload']['name'] : $project['image_upload'];
    $design_file = !empty($_FILES['design_file']['name']) ? $_FILES['design_file']['name'] : $project['design_file'];

    $upload_dir = 'C:/xampp/htdocs/OMC/projects/project_files/' . $project_name . '/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    if (!empty($_FILES['file_upload']['name'][0])) {
        $file_upload_paths = [];
        foreach ($_FILES['file_upload']['name'] as $key => $name) {
            $tmp_name = $_FILES['file_upload']['tmp_name'][$key];
            $file_upload_path = $upload_dir . $name;
            move_uploaded_file($tmp_name, $file_upload_path);
            $file_upload_paths[] = basename($file_upload_path);
        }
        $file_uploads = implode(',', $file_upload_paths);
    } else {
        $file_uploads = is_array($file_uploads) ? implode(',', $file_uploads) : $file_uploads;
    }

    if (!empty($_FILES['image_upload']['name'])) {
        $image_upload_path = $upload_dir . $image_upload;
        move_uploaded_file($_FILES['image_upload']['tmp_name'], $image_upload_path);
        $image_upload = basename($image_upload_path);
    }

    if (!empty($_FILES['design_file']['name'])) {
        $design_file_path = $upload_dir . $design_file;
        move_uploaded_file($_FILES['design_file']['tmp_name'], $design_file_path);
        $design_file = basename($design_file_path);
    }

    // Update project with file paths if files were uploaded
    $controller->updateProject(
        $_POST['id'],
        $project_name,
        $_POST['design_date'],
        $_POST['customer_name'],
        $_POST['laser_time'],
        $_POST['router_time'],
        $_POST['labor_hours'],
        $_POST['project_description'],
        $_POST['due_date'],
        $file_uploads,
        $image_upload,
        $design_file
    );

    header('Location: ../../Views/projects/list_projects.php'); // Corrected redirect path
    exit;
}
?>

<?php include '../../Views/header.php'; ?>

<?php if ($project): ?>
    <h1 class="title" style="margin-top: 200px;">Edit Project</h1> <!-- Add margin to bring the title down -->
    <form id="project-form" action="../../Views/projects/edit_projects.php" method="post" enctype="multipart/form-data"> <!-- Update form action -->
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($project['id']); ?>">
        <div class="submit-container" style="justify-content: space-between; width: 100%;">
            <button type="button" class="btn styled-btn" onclick="window.location.href='../../Views/projects/list_projects.php'" style="margin-left: 0;">Cancel</button> <!-- Update cancel button -->
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
                <input type="file" id="file_upload" name="file_upload[]" multiple>
                <?php if (!empty($project['file_upload'])): ?>
                    <p>Current files: <?php echo htmlspecialchars($project['file_upload']); ?></p>
                <?php endif; ?>
                <label for="image_upload">Image Upload:</label>
                <input type="file" id="image_upload" name="image_upload" accept=".bmp,.jpg,.jpeg,.tiff,.gif,.png">
                <?php if (!empty($project['image_upload'])): ?>
                    <p>Current image: <?php echo htmlspecialchars($project['image_upload']); ?></p>
                <?php endif; ?>
                <label for="design_file">Design File:</label>
                <input type="file" id="design_file" name="design_file">
                <?php if (!empty($project['design_file'])): ?>
                    <p>Current design file: <?php echo htmlspecialchars($project['design_file']); ?></p>
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