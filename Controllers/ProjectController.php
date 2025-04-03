<?php
namespace MyApp\Controllers;

use PDO; // Import the PDO class
use PDOException; // Import the PDOException class
use MyApp\Models\ProjectModel; // Import the ProjectModel class

require_once realpath(dirname(__FILE__) . '/../config.php'); // Corrected path to config.php
require_once BASE_PATH . '/Models/ProjectModel.php'; // Corrected path to ProjectModel.php

class ProjectController {
    private PDO $database;

    public function __construct(PDO $database) { // Ensure $database is a PDO instance
        $this->database = $database; // Store the PDO instance
    }

    public function getProjectById($project_id) { // Use 'project_id'
        $projectModel = new ProjectModel($this->database); // Use the correct namespace
        return $projectModel->getProjectById($project_id); // Use 'project_id'
    }

    public function updateProject($id, $projectName, $designDate, $customerName, $laserTime, $routerTime, $laborHours, $projectDescription, $dueDate, $fileUpload, $imageUpload) {
        $projectModel = new ProjectModel($this->database); // Use the correct namespace
        return $projectModel->updateProject($id, $projectName, $designDate, $customerName, $laserTime, $routerTime, $laborHours, $projectDescription, $dueDate, $fileUpload, $imageUpload);
    }

    public function deleteProject($project_id) { // Use 'project_id'
        if ($this->database === null) {
            throw new \Exception("Database connection is not initialized.");
        }

        try {
            $this->database->beginTransaction();

            // Fetch the customer associated with the project
            $query = "SELECT customer_id FROM projects WHERE project_id = :project_id";
            $stmt = $this->database->prepare($query);
            $stmt->bindValue(':project_id', $project_id, PDO::PARAM_INT);
            $stmt->execute();
            $project = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($project) {
                $customer_id = $project['customer_id'];

                // Fetch the current project_ids for the customer
                $query = "SELECT project_ids FROM customers WHERE customer_id = :customer_id";
                $stmt = $this->database->prepare($query);
                $stmt->bindValue(':customer_id', $customer_id, PDO::PARAM_INT);
                $stmt->execute();
                $customer = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($customer) {
                    $project_ids = json_decode($customer['project_ids'], true) ?? [];
                    $updated_project_ids = array_filter($project_ids, function ($id) use ($project_id) {
                        return $id != $project_id; // Remove the project ID
                    });

                    // Update the customer's project_ids column
                    $query = "UPDATE customers SET project_ids = :project_ids WHERE customer_id = :customer_id";
                    $stmt = $this->database->prepare($query);
                    $stmt->bindValue(':project_ids', json_encode($updated_project_ids), PDO::PARAM_STR);
                    $stmt->bindValue(':customer_id', $customer_id, PDO::PARAM_INT);
                    $stmt->execute();
                }
            }

            // Delete the project from the `projects` table
            $query = "DELETE FROM projects WHERE project_id = :project_id";
            $stmt = $this->database->prepare($query);
            $stmt->bindValue(':project_id', $project_id, PDO::PARAM_INT);
            $stmt->execute();

            $this->database->commit();
            return true;
        } catch (PDOException $e) { // Simplified from \PDOException
            $this->database->rollBack();
            throw new \Exception("Failed to delete project: " . $e->getMessage());
        }
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
        $design_file,
        $customer_id
    ) {
        if ($this->database === null) {
            throw new \Exception("Database connection is not initialized.");
        }

        try {
            // Add the project to the `projects` table
            $query = "INSERT INTO projects (
                project_name, 
                design_date, 
                customer_id, 
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
                :customer_id, 
                :laser_time, 
                :router_time, 
                :labor_hours, 
                :project_description, 
                :due_date, 
                :file_upload, 
                :image_upload, 
                :design_file
            )";

            $stmt = $this->database->prepare($query);
            $stmt->bindValue(':project_name', $project_name, PDO::PARAM_STR);
            $stmt->bindValue(':design_date', $design_date, PDO::PARAM_STR);
            $stmt->bindValue(':customer_id', $customer_id, PDO::PARAM_INT); // Use the customer ID
            $stmt->bindValue(':laser_time', $laser_time, PDO::PARAM_INT);
            $stmt->bindValue(':router_time', $router_time, PDO::PARAM_INT);
            $stmt->bindValue(':labor_hours', $labor_hours, PDO::PARAM_INT);
            $stmt->bindValue(':project_description', $project_description, PDO::PARAM_STR);
            $stmt->bindValue(':due_date', $due_date, PDO::PARAM_STR);
            $stmt->bindValue(':file_upload', $file_upload, PDO::PARAM_STR);
            $stmt->bindValue(':image_upload', $image_upload, PDO::PARAM_STR);
            $stmt->bindValue(':design_file', $design_file, PDO::PARAM_STR);

            $stmt->execute();
            $project_id = $this->database->lastInsertId(); // Get the new project ID

            // Update the `customers` table with the project name and ID
            $updateQuery = "UPDATE customers SET project = :project_name, proj_id = :project_id WHERE customer_id = :customer_id";
            $updateStmt = $this->database->prepare($updateQuery);
            $updateStmt->bindValue(':project_name', $project_name, PDO::PARAM_STR);
            $updateStmt->bindValue(':project_id', $project_id, PDO::PARAM_INT);
            $updateStmt->bindValue(':customer_id', $customer_id, PDO::PARAM_INT);
            $updateStmt->execute();

            return $project_id; // Return the new project ID
        } catch (PDOException $e) { // Simplified from \PDOException
            throw new \Exception("Failed to add project: " . $e->getMessage());
        }
    }

    public function deleteProjectByName($projectName) {
        if ($this->database === null) {
            throw new \Exception("Database connection is not initialized.");
        }

        try {
            // Delete the project from the `projects` table
            $queryProject = "DELETE FROM projects WHERE project_name = :project_name";
            $stmtProject = $this->database->prepare($queryProject);
            $stmtProject->bindValue(':project_name', $projectName, PDO::PARAM_STR);
            return $stmtProject->execute();
        } catch (PDOException $e) {
            throw new \Exception("Failed to delete project: " . $e->getMessage());
        }
    }

    public function listProjects() {
        if ($this->database === null) {
            throw new \Exception("Database connection is not initialized.");
        }

        $query = "SELECT p.*, c.name AS customer_name 
                  FROM projects p
                  LEFT JOIN customers c ON p.customer_id = c.customer_id"; // Join with customers table
        $stmt = $this->database->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllProjects() {
        if ($this->database === null) {
            throw new \Exception("Database connection is not initialized.");
        }

        $query = "SELECT * FROM projects";
        $stmt = $this->database->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getProjectsByCustomerName($customerName) {
        if ($this->database === null) {
            throw new \Exception("Database connection is not initialized.");
        }

        // Query to fetch projects linked to a customer by name
        $query = "SELECT p.* 
                  FROM projects p
                  JOIN customers c ON p.customer_id = c.customer_id
                  WHERE c.name = :customer_name";
        $stmt = $this->database->prepare($query);
        $stmt->bindValue(':customer_name', $customerName, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Fetch all matching projects
    }

    public function updateProjectCustomer($projectId, $customerId) {
        $projectModel = new ProjectModel($this->database);
        return $projectModel->updateProjectCustomer($projectId, $customerId); // Use 'customer_id'
    }

    public function getProjectsByCustomerId($customerId) {
        $query = "SELECT * FROM projects WHERE customer_id = :customer_id";
        $stmt = $this->database->prepare($query);
        $stmt->bindValue(':customer_id', $customerId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function assignProjectToCustomer($customerId, $projectId) {
        $projectModel = new ProjectModel($this->database);
        $projectModel->assignProjectToCustomer($customerId, $projectId);
    }

    public function getCustomersByProjectId($projectId) {
        $projectModel = new ProjectModel($this->database);
        return $projectModel->getCustomersByProjectId($projectId);
    }

    public function addProjectWithCustomers($projectData, $customerIds) {
        $this->database->beginTransaction();
        try {
            // Add the project with all required arguments, including $design_file
            $projectId = $this->addProject(
                $projectData['project_name'],
                $projectData['design_date'],
                $projectData['customer_name'],
                $projectData['laser_time'],
                $projectData['router_time'],
                $projectData['labor_hours'],
                $projectData['project_description'],
                $projectData['due_date'],
                $projectData['file_upload'],
                $projectData['image_upload'],
                $projectData['design_file'], // Ensure $design_file is passed
                $projectData['customer_id'] // Ensure $customer_id is passed
            );

            // Assign customers to the project
            foreach ($customerIds as $customerId) {
                $this->assignProjectToCustomer($customerId, $projectId);
            }

            $this->database->commit();
            return $projectId;
        } catch (\Exception $e) {
            $this->database->rollBack();
            throw $e;
        }
    }

    public function searchProjects($searchTerm) {
        if ($this->database === null) {
            throw new \Exception("Database connection is not initialized.");
        }

        $query = "SELECT p.*, c.name AS customer_name 
                  FROM projects p
                  LEFT JOIN customers c ON p.customer_id = c.customer_id
                  WHERE p.project_name LIKE :search_term 
                  OR p.project_description LIKE :search_term 
                  OR c.name LIKE :search_term"; // Search in project name, description, and customer name
        $stmt = $this->database->prepare($query);
        $stmt->bindValue(':search_term', '%' . $searchTerm . '%', PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Fetch all matching rows
    }

    public function getProjectByName($projectName) {
        $projectModel = new ProjectModel($this->database); // Use the ProjectModel
        return $projectModel->getProjectByName($projectName); // Call the model's method
    }

    // Add other methods as needed
}
?>