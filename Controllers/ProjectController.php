<?php
namespace MyApp\Controllers;

use PDO; // Import the PDO class
use MyApp\Models\ProjectModel; // Import the ProjectModel class

require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php'; // Corrected path to config.php
require_once BASE_PATH . '/Models/ProjectModel.php'; // Corrected path to ProjectModel.php

class ProjectController {
    private $database;

    public function __construct($database) {
        $this->database = $database; // Store the database object
    }

    public function getProjectById($projectId) {
        $projectModel = new ProjectModel($this->database); // Use the correct namespace
        return $projectModel->getProjectById($projectId);
    }

    public function updateProject($projectId, $projectName, $designDate, $customerName, $laserTime, $routerTime, $laborHours, $projectDescription, $dueDate, $fileUpload, $imageUpload) {
        $projectModel = new ProjectModel($this->database); // Use the correct namespace
        return $projectModel->updateProject($projectId, $projectName, $designDate, $customerName, $laserTime, $routerTime, $laborHours, $projectDescription, $dueDate, $fileUpload, $imageUpload);
    }

    public function deleteProject($projectId) {
        $projectModel = new ProjectModel($this->database); // Use the correct namespace
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

            $stmt = $this->database->getConnection()->prepare($query);
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
            return $this->database->getConnection()->lastInsertId(); // Return the new project ID
        } catch (\PDOException $e) {
            throw new \Exception("Failed to add project: " . $e->getMessage());
        }
    }

    public function searchProjects($searchTerm) {
        $query = "SELECT * FROM projects WHERE project_name LIKE :search_term OR project_description LIKE :search_term";
        $stmt = $this->database->getConnection()->prepare($query); // Use the PDO connection
        $likeTerm = '%' . $searchTerm . '%';
        $stmt->bindValue(':search_term', $likeTerm, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Fetch all matching rows
    }

    public function getProjectByName($projectName) {
        $query = "SELECT * FROM projects WHERE project_name = :project_name LIMIT 1";
        $stmt = $this->database->getConnection()->prepare($query);
        $stmt->bindValue(':project_name', $projectName, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function deleteProjectByName($projectName) {
        try {
            // Delete the project from the `projects` table
            $queryProject = "DELETE FROM projects WHERE project_name = :project_name";
            $stmtProject = $this->database->getConnection()->prepare($queryProject);
            $stmtProject->bindValue(':project_name', $projectName, PDO::PARAM_STR);
            return $stmtProject->execute();
        } catch (\PDOException $e) {
            throw new \Exception("Failed to delete project: " . $e->getMessage());
        }
    }

    public function listProjects() {
        $query = "SELECT * FROM projects";
        $stmt = $this->database->getConnection()->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllProjects() {
        $query = "SELECT * FROM projects";
        $stmt = $this->database->getConnection()->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    // Add other methods as needed
}
?>