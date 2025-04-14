<?php
namespace MyApp\Controllers;

use PDO; // Import the PDO class
use PDOException; // Import the PDOException class
use MyApp\Models\ProjectModel; // Import the ProjectModel class

require_once realpath(dirname(__FILE__) . '/../config.php'); // Corrected path to config.php
require_once BASE_PATH . '/Models/ProjectModel.php'; // Corrected path to ProjectModel.php

class ProjectController {
    private $db;

    public function __construct(PDO $database) { // Ensure $database is a PDO instance
        $this->db = $database; // Store the PDO instance
    }

    public function getProjectById($projectId) {
        $projectModel = new ProjectModel($this->db); // Use the correct namespace
        return $projectModel->getProjectById($projectId);
    }

    public function updateProject($projectId, $projectName, $designDate, $customerName, $laserTime, $routerTime, $laborHours, $projectDescription, $dueDate, $fileUpload, $imageUpload) {
        $projectModel = new ProjectModel($this->db); // Use the correct namespace
        return $projectModel->updateProject($projectId, $projectName, $designDate, $customerName, $laserTime, $routerTime, $laborHours, $projectDescription, $dueDate, $fileUpload, $imageUpload);
    }

    public function deleteProject($projectId) {
        $projectModel = new ProjectModel($this->db); // Use the correct namespace
        return $projectModel->deleteProject($projectId); // Call the model's deleteProject method
    }

    public function addProject(
        $project_name,
        $design_date,
        $customer_name,
        $laser_time,
        $router_time,
        $labor_hours,
        $project_description,
        $due_date,
        $file_upload,
        $image_upload,
        $design_file
    ) {
        if ($this->db === null) {
            throw new \Exception("Database connection is not initialized.");
        }

        try {
            $query = "INSERT INTO projects (
                project_name, 
                design_date, 
                customer_name, 
                laser_time, 
                router_time, 
                labor_hours, 
                project_description, 
                due_date, 
                file_upload, 
                image_upload, 
                design_file
            ) VALUES (
                :project_name, 
                :design_date, 
                :customer_name, 
                :laser_time, 
                :router_time, 
                :labor_hours, 
                :project_description, 
                :due_date, 
                :file_upload, 
                :image_upload, 
                :design_file
            )";

            $stmt = $this->db->prepare($query); // Use the PDO instance directly
            $stmt->bindValue(':project_name', $project_name, PDO::PARAM_STR);
            $stmt->bindValue(':design_date', $design_date, PDO::PARAM_STR);
            $stmt->bindValue(':customer_name', $customer_name, PDO::PARAM_STR);
            $stmt->bindValue(':laser_time', $laser_time, PDO::PARAM_INT);
            $stmt->bindValue(':router_time', $router_time, PDO::PARAM_INT);
            $stmt->bindValue(':labor_hours', $labor_hours, PDO::PARAM_INT);
            $stmt->bindValue(':project_description', $project_description, PDO::PARAM_STR);
            $stmt->bindValue(':due_date', $due_date, PDO::PARAM_STR);
            $stmt->bindValue(':file_upload', $file_upload, PDO::PARAM_STR);
            $stmt->bindValue(':image_upload', $image_upload, PDO::PARAM_STR);
            $stmt->bindValue(':design_file', $design_file, PDO::PARAM_STR);

            $stmt->execute();
            return $this->db->lastInsertId(); // Return the new project ID
        } catch (PDOException $e) {
            throw new \Exception("Failed to add project: " . $e->getMessage());
        }
    }

    public function searchProjects($searchTerm) {
        if ($this->db === null) {
            throw new \Exception("Database connection is not initialized.");
        }

        $query = "SELECT * FROM projects WHERE project_name LIKE :search_term OR project_description LIKE :search_term";
        $stmt = $this->db->prepare($query); // Use the PDO instance directly
        $likeTerm = '%' . $searchTerm . '%';
        $stmt->bindValue(':search_term', $likeTerm, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Fetch all matching rows
    }

    public function getProjectByName($projectName) {
        if ($this->db === null) {
            throw new \Exception("Database connection is not initialized.");
        }

        $query = "SELECT * FROM projects WHERE project_name = :project_name LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':project_name', $projectName, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function deleteProjectByName($projectName) {
        if ($this->db === null) {
            throw new \Exception("Database connection is not initialized.");
        }

        try {
            // Delete the project from the `projects` table
            $queryProject = "DELETE FROM projects WHERE project_name = :project_name";
            $stmtProject = $this->db->prepare($queryProject);
            $stmtProject->bindValue(':project_name', $projectName, PDO::PARAM_STR);
            return $stmtProject->execute();
        } catch (PDOException $e) {
            throw new \Exception("Failed to delete project: " . $e->getMessage());
        }
    }

    public function listProjects() {
        if ($this->db === null) {
            throw new \Exception("Database connection is not initialized.");
        }

        $query = "SELECT * FROM projects";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllProjects() {
        if ($this->db === null) {
            throw new \Exception("Database connection is not initialized.");
        }

        $query = "SELECT * FROM projects";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Add other methods as needed
}
?>