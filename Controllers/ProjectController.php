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

    public function getProjectByName($project_name) {
        $conn = $this->db->getConnection();
        $query = "SELECT * FROM projects WHERE project_name = :project_name";
        $stmt = $conn->prepare($query);
        $stmt->bindValue(':project_name', $project_name, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getProjectById($project_id) {
        $conn = $this->db->getConnection();
        $query = "SELECT * FROM projects WHERE project_id = :project_id"; // Correct column name
        $stmt = $conn->prepare($query);
        $stmt->bindValue(':project_id', $project_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateProject($project_name, $design_date, $customer_name, $laser_time, $router_time, $labor_hours, $project_description, $due_date, $file_upload, $image_upload, $design_file) {
        $conn = $this->db->getConnection();
        $existingProject = $this->getProjectByName($project_name);
        $file_upload = !empty($file_upload) ? (is_array($file_upload) ? implode(',', $file_upload) : $file_upload) : $existingProject['file_upload'];
        $image_upload = !empty($image_upload) ? (is_array($image_upload) ? implode(',', $image_upload) : $image_upload) : $existingProject['image_upload'];
        $design_file = !empty($design_file) ? $design_file : $existingProject['design_file'];

        $query = "UPDATE projects SET 
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
                  WHERE project_name = :project_name";
        $stmt = $conn->prepare($query);
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
        $stmt->bindValue(':project_name', $project_name);
        $stmt->execute();
    }

    public function deleteProjectByName($project_name) {
        $conn = $this->db->getConnection();
        try {
            // Begin transaction
            $conn->beginTransaction();

            // Delete related records in the bom table
            $stmt = $conn->prepare("DELETE FROM bom WHERE project_name = :project_name");
            $stmt->bindParam(':project_name', $project_name, PDO::PARAM_STR);
            $stmt->execute();

            // Check if the estimate table exists and delete related records
            $stmt = $conn->prepare("SHOW TABLES LIKE 'estimate'");
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                $stmt = $conn->prepare("DELETE FROM estimate WHERE project_name = :project_name");
                $stmt->bindParam(':project_name', $project_name, PDO::PARAM_STR);
                $stmt->execute();
            }

            // Delete the project
            $stmt = $conn->prepare("DELETE FROM projects WHERE project_name = :project_name");
            $stmt->bindParam(':project_name', $project_name, PDO::PARAM_STR);
            $stmt->execute();

            // Commit transaction
            $conn->commit();
        } catch (\PDOException $e) {
            // Rollback transaction if something failed
            $conn->rollBack();
            throw $e;
        }
    }

    public function deleteProject($project_id) {
        $conn = $this->db->getConnection();
        try {
            // Begin transaction
            $conn->beginTransaction();

            // Delete related records in the bom table
            $stmt = $conn->prepare("DELETE FROM bom WHERE project_id = :project_id");
            $stmt->bindParam(':project_id', $project_id, PDO::PARAM_INT);
            $stmt->execute();

            // Check if the estimate table exists and delete related records
            $stmt = $conn->prepare("SHOW TABLES LIKE 'estimate'");
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                $stmt = $conn->prepare("DELETE FROM estimate WHERE project_id = :project_id");
                $stmt->bindParam(':project_id', $project_id, PDO::PARAM_INT);
                $stmt->execute();
            }

            // Delete the project
            $stmt = $conn->prepare("DELETE FROM projects WHERE project_id = :project_id");
            $stmt->bindParam(':project_id', $project_id, PDO::PARAM_INT);
            $stmt->execute();

            // Commit transaction
            $conn->commit();
        } catch (\PDOException $e) {
            // Rollback transaction if something failed
            $conn->rollBack();
            throw $e;
        }
    }

    public function searchProjects($searchTerm) {
        $conn = $this->db->getConnection();
        $query = "SELECT * FROM projects WHERE project_name LIKE :search_term OR customer_name LIKE :search_term";
        $stmt = $conn->prepare($query);
        $searchTerm = "%$searchTerm%";
        $stmt->bindParam(':search_term', $searchTerm, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function searchProjectsByName($searchTerm) {
        $conn = $this->db->getConnection();
        $query = "SELECT * FROM projects WHERE project_name LIKE :search_term OR customer_name LIKE :search_term";
        $stmt = $conn->prepare($query);
        $searchTerm = "%$searchTerm%";
        $stmt->bindParam(':search_term', $searchTerm, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addProject($project_name, $design_date, $customer_name, $laser_time, $router_time, $labor_hours, $project_description, $due_date, $file_upload, $image_upload, $design_file) {
        $conn = $this->db->getConnection();
        // Ensure project name is unique
        $existingProject = $this->getProjectByName($project_name);
        if ($existingProject) {
            throw new \Exception("Project name must be unique.");
        }
        $file_upload = is_array($file_upload) ? implode(',', $file_upload) : $file_upload;
        $image_upload = is_array($image_upload) ? implode(',', $image_upload) : $image_upload;
        $design_file = is_array($design_file) ? implode(',', $design_file) : $design_file;

        $query = "INSERT INTO projects (project_name, design_date, customer_name, laser_time, router_time, labor_hours, project_description, due_date, file_upload, image_upload, design_file) 
                  VALUES (:project_name, :design_date, :customer_name, :laser_time, :router_time, :labor_hours, :project_description, :due_date, :file_upload, :image_upload, :design_file)";
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
        $stmt->execute();

        return $conn->lastInsertId();
    }
}
?>