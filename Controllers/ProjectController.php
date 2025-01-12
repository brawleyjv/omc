<?php
namespace MyApp\Controllers;

use MyApp\Models\Database;
use MyApp\Models\Project;
use PDO;

class ProjectController {
    private $db;

    public function __construct(Database $database) {
        $this->db = $database;
    }

    public function listProjects() {
        $conn = $this->db->getConnection();
        $query = "SELECT * FROM projects";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getProjectById($id) {
        $conn = $this->db->getConnection();
        $query = "SELECT * FROM projects WHERE id = :id";
        $stmt = $conn->prepare($query);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateProject($id, $project_name, $design_date, $customer_name, $laser_time, $router_time, $labor_hours, $project_description, $due_date, $file_upload, $image_upload, $design_file) {
        $conn = $this->db->getConnection();
        $existingProject = $this->getProjectById($id);
        $file_upload = !empty($file_upload) ? (is_array($file_upload) ? implode(',', $file_upload) : $file_upload) : $existingProject['file_upload'];
        $image_upload = !empty($image_upload) ? (is_array($image_upload) ? implode(',', $image_upload) : $image_upload) : $existingProject['image_upload'];
        $design_file = !empty($design_file) ? $design_file : $existingProject['design_file'];

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
                    image_upload = :image_upload,
                    design_file = :design_file
                  WHERE id = :id";
        $stmt = $conn->prepare($query);
        $stmt->bindValue(':project_name', $project_name);
        $stmt->bindValue(':design_date', $design_date);
        $stmt->bindValue(':customer_name', $customer_name);
        $stmt->bindValue(':laser_time', $laser_time);
        $stmt->bindValue(':router_time', $router_time);
        $stmt->bindValue(':labor_hours', $labor_hours);
        $stmt->bindValue(':project_description', $project_description);
        $stmt->bindValue(':due_date', $due_date);
        $stmt->bindValue(':file_upload', $file_upload);
        $stmt->bindValue(':image_upload', $image_upload);
        $stmt->bindValue(':design_file', $design_file);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function deleteProject($id) {
        $conn = $this->db->getConnection();
        error_log("Attempting to delete project with ID: $id");
        $query = "DELETE FROM projects WHERE id = :id";
        $stmt = $conn->prepare($query);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        if ($stmt->execute()) {
            error_log("Project with ID $id deleted successfully.");
        } else {
            error_log("Failed to delete project with ID $id. Error: " . implode(", ", $stmt->errorInfo()));
        }
    }

    public function searchProjects($searchTerm) {
        $conn = $this->db->getConnection();
        $query = "SELECT * FROM projects WHERE id LIKE :search_term OR customer_name LIKE :search_term OR project_name LIKE :search_term";
        $stmt = $conn->prepare($query);
        $searchTerm = "%$searchTerm%";
        $stmt->bindParam(':search_term', $searchTerm, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addProject($project_name, $design_date, $customer_name, $laser_time, $router_time, $labor_hours, $project_description, $due_date, $file_upload, $image_upload, $design_file) {
        $project = new Project($project_name, $design_date, $customer_name, $laser_time, $router_time, $labor_hours, $project_description, $due_date, $file_upload, $image_upload, $design_file);
        return $this->db->insertProject($project);
    }
}
?>