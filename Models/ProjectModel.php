<?php
namespace MyApp\Models;

use PDO;

class ProjectModel {
    private $database;

    public function __construct(PDO $database) { // Ensure $database is a PDO instance
        $this->database = $database; // Store the PDO instance directly
    }

    public function getProjectById($projectId) {
        $query = "SELECT * FROM projects WHERE id = :id";
        $stmt = $this->database->prepare($query); // Use the PDO instance directly
        $stmt->bindValue(':id', $projectId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getProjectByName($project_name) {
        $stmt = $this->database->prepare('SELECT * FROM projects WHERE project_name = :project_name');
        $stmt->bindValue(':project_name', $project_name, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(); // No argument needed
    }

    public function updateProject($projectId, $projectName, $designDate, $customerName, $laserTime, $routerTime, $laborHours, $projectDescription, $dueDate, $fileUpload, $imageUpload) {
        $query = "UPDATE projects SET 
            project_name = :project_name,
            design_date = :design_date,
            customer_name = :customer_name,
            laser_time = :laser_time,
            router_time = :router_time,
            labor_hours = :labor_hours,
            project_description = :project_description,
            due_date = :due_date,
            file_upload = :file_upload,
            image_upload = :image_upload
            WHERE id = :id";

        $stmt = $this->database->prepare($query); // Use the PDO instance directly
        $stmt->bindValue(':id', $projectId, PDO::PARAM_INT);
        $stmt->bindValue(':project_name', $projectName, PDO::PARAM_STR);
        $stmt->bindValue(':design_date', $designDate, PDO::PARAM_STR);
        $stmt->bindValue(':customer_name', $customerName, PDO::PARAM_STR);
        $stmt->bindValue(':laser_time', $laserTime, PDO::PARAM_INT);
        $stmt->bindValue(':router_time', $routerTime, PDO::PARAM_INT);
        $stmt->bindValue(':labor_hours', $laborHours, PDO::PARAM_INT);
        $stmt->bindValue(':project_description', $projectDescription, PDO::PARAM_STR);
        $stmt->bindValue(':due_date', $dueDate, PDO::PARAM_STR);
        $stmt->bindValue(':file_upload', $fileUpload, PDO::PARAM_STR);
        $stmt->bindValue(':image_upload', $imageUpload, PDO::PARAM_STR);

        return $stmt->execute();
    }

    public function deleteProject($projectId) {
        $stmt = $this->database->prepare('DELETE FROM projects WHERE id = :id');
        return $stmt->execute([':id' => $projectId]);
    }

    /**
     * Get project with its linked estimate
     */
    public function getProjectWithEstimate($projectId) {
        $query = "SELECT p.*, e.estimate_number, e.total_estimate, e.status as estimate_status
                  FROM projects p
                  LEFT JOIN estimates e ON p.estimate_id = e.id
                  WHERE p.id = :id";
        $stmt = $this->database->prepare($query);
        $stmt->bindValue(':id', $projectId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Update project's estimate_id
     */
    public function linkEstimate($projectId, $estimateId) {
        $query = "UPDATE projects SET estimate_id = :estimate_id WHERE id = :id";
        $stmt = $this->database->prepare($query);
        return $stmt->execute([
            ':estimate_id' => $estimateId,
            ':id' => $projectId
        ]);
    }

    /**
     * Check if project has an estimate
     */
    public function hasEstimate($projectId) {
        $query = "SELECT estimate_id FROM projects WHERE id = :id";
        $stmt = $this->database->prepare($query);
        $stmt->bindValue(':id', $projectId, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return !empty($result['estimate_id']);
    }
}
?>
