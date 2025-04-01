<?php
namespace MyApp\Models;

require_once realpath(dirname(__FILE__) . '/../config.php');

use PDO;

class ProjectModel {
    private $conn;

    public function __construct($database) {
        $this->conn = $database->getConnection();
    }

    public function getProjectById($projectId) {
        $stmt = $this->conn->prepare('SELECT * FROM projects WHERE id = :id');
        $stmt->bindValue(':id', $projectId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(); // No argument needed
    }

    public function getProjectByName($project_name) {
        $stmt = $this->conn->prepare('SELECT * FROM projects WHERE project_name = :project_name');
        $stmt->bindValue(':project_name', $project_name, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(); // No argument needed
    }

    public function updateProject($projectId, $projectName, $designDate, $customerName, $laserTime, $routerTime, $laborHours, $projectDescription, $dueDate, $fileUpload, $imageUpload) {
        $stmt = $this->conn->prepare('
            UPDATE projects 
            SET 
                project_name = :name, 
                design_date = :design_date, 
                customer_name = :customer_name, 
                laser_time = :laser_time, 
                router_time = :router_time, 
                labor_hours = :labor_hours, 
                project_description = :description, 
                due_date = :due_date, 
                file_upload = :file_upload, 
                image_upload = :image_upload 
            WHERE project_name = :project_name
        ');
        return $stmt->execute([
            ':project_name' => $projectId, // Ensure this matches the WHERE clause
            ':name' => $projectName,
            ':design_date' => $designDate,
            ':customer_name' => $customerName,
            ':laser_time' => $laserTime,
            ':router_time' => $routerTime,
            ':labor_hours' => $laborHours,
            ':description' => $projectDescription,
            ':due_date' => $dueDate,
            ':file_upload' => $fileUpload,
            ':image_upload' => $imageUpload
        ]);
    }

    public function deleteProject($projectId) {
        $stmt = $this->conn->prepare('DELETE FROM projects WHERE id = :id');
        return $stmt->execute([':id' => $projectId]);
    }
}
?>
