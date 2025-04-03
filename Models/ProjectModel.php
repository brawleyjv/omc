<?php
namespace MyApp\Models;

use PDO;
use PDOException; // Import the PDOException class

class ProjectModel {
    private $database;

    public function __construct(PDO $database) { // Ensure $database is a PDO instance
        $this->database = $database; // Store the PDO instance directly
    }

    public function getProjectById($project_id) { // Use 'project_id'
        $query = "SELECT * FROM projects WHERE project_id = :project_id"; // Use 'project_id'
        $stmt = $this->database->prepare($query); // Use the PDO instance directly
        $stmt->bindValue(':project_id', $project_id, PDO::PARAM_INT); // Use 'project_id'
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
        $this->database->beginTransaction(); // Start a transaction

        try {
            // Check if the customer already exists in the `customers` table
            $query = "SELECT customer_id FROM customers WHERE name = :customer_name LIMIT 1";
            $stmt = $this->database->prepare($query);
            $stmt->bindValue(':customer_name', $customerName, PDO::PARAM_STR);
            $stmt->execute();
            $customer = $stmt->fetch(PDO::FETCH_ASSOC);

            // If the customer doesn't exist, add them to the `customers` table
            if (!$customer) {
                $query = "INSERT INTO customers (name) VALUES (:customer_name)";
                $stmt = $this->database->prepare($query);
                $stmt->bindValue(':customer_name', $customerName, PDO::PARAM_STR);
                $stmt->execute();
                $customerId = $this->database->lastInsertId(); // Get the new customer ID
            } else {
                $customerId = $customer['customer_id']; // Use the existing customer ID
            }

            // Update the project in the `projects` table
            $query = "UPDATE projects SET 
                project_name = :project_name,
                design_date = :design_date,
                customer_id = :customer_id,
                laser_time = :laser_time,
                router_time = :router_time,
                labor_hours = :labor_hours,
                project_description = :project_description,
                due_date = :due_date,
                file_upload = :file_upload,
                image_upload = :image_upload
                WHERE project_id = :project_id";

            $stmt = $this->database->prepare($query);
            $stmt->bindValue(':project_id', $projectId, PDO::PARAM_INT);
            $stmt->bindValue(':project_name', $projectName, PDO::PARAM_STR);
            $stmt->bindValue(':design_date', $designDate, PDO::PARAM_STR);
            $stmt->bindValue(':customer_id', $customerId, PDO::PARAM_INT); // Use the customer ID
            $stmt->bindValue(':laser_time', $laserTime, PDO::PARAM_INT);
            $stmt->bindValue(':router_time', $routerTime, PDO::PARAM_INT);
            $stmt->bindValue(':labor_hours', $laborHours, PDO::PARAM_INT);
            $stmt->bindValue(':project_description', $projectDescription, PDO::PARAM_STR);
            $stmt->bindValue(':due_date', $dueDate, PDO::PARAM_STR);
            $stmt->bindValue(':file_upload', $fileUpload, PDO::PARAM_STR);
            $stmt->bindValue(':image_upload', $imageUpload, PDO::PARAM_STR);

            $stmt->execute();
            $this->database->commit(); // Commit the transaction
            return true;
        } catch (PDOException $e) { // Simplified from \PDOException
            $this->database->rollBack(); // Roll back the transaction on error
            throw new \Exception("Failed to update project: " . $e->getMessage());
        }
    }

    public function updateProjectCustomer($projectId, $customerId) { // Update customer reference
        $query = "UPDATE projects SET customer_id = :customer_id WHERE project_id = :project_id"; // Use 'customer_id'
        $stmt = $this->database->prepare($query);
        $stmt->bindValue(':customer_id', $customerId, PDO::PARAM_INT);
        $stmt->bindValue(':project_id', $projectId, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function deleteProject($projectId) {
        $stmt = $this->database->prepare('DELETE FROM projects WHERE project_id = :project_id');
        return $stmt->execute([':project_id' => $projectId]);
    }

    public function getProjectsByCustomerId($customerId) {
        $query = "SELECT * FROM projects WHERE customer_id = :customer_id";
        $stmt = $this->database->prepare($query);
        $stmt->bindValue(':customer_id', $customerId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCustomersByProjectId($projectId) {
        $query = "SELECT c.* 
                  FROM customers c
                  JOIN projects p ON c.customer_id = p.customer_id
                  WHERE p.project_id = :project_id";
        $stmt = $this->database->prepare($query);
        $stmt->bindValue(':project_id', $projectId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function assignProjectToCustomer($customerId, $projectId) {
        $query = "UPDATE projects SET customer_id = :customer_id WHERE project_id = :project_id";
        $stmt = $this->database->prepare($query);
        $stmt->bindValue(':customer_id', $customerId, PDO::PARAM_INT);
        $stmt->bindValue(':project_id', $projectId, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function addProject($projectData) {
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
        $stmt->bindValue(':project_name', $projectData['project_name'], PDO::PARAM_STR);
        $stmt->bindValue(':design_date', $projectData['design_date'], PDO::PARAM_STR);
        $stmt->bindValue(':customer_id', $projectData['customer_id'], PDO::PARAM_INT); // Associate with customer_id
        $stmt->bindValue(':laser_time', $projectData['laser_time'], PDO::PARAM_INT);
        $stmt->bindValue(':router_time', $projectData['router_time'], PDO::PARAM_INT);
        $stmt->bindValue(':labor_hours', $projectData['labor_hours'], PDO::PARAM_INT);
        $stmt->bindValue(':project_description', $projectData['project_description'], PDO::PARAM_STR);
        $stmt->bindValue(':due_date', $projectData['due_date'], PDO::PARAM_STR);
        $stmt->bindValue(':file_upload', $projectData['file_upload'], PDO::PARAM_STR);
        $stmt->bindValue(':image_upload', $projectData['image_upload'], PDO::PARAM_STR);
        $stmt->bindValue(':design_file', $projectData['design_file'], PDO::PARAM_STR);

        $stmt->execute();
        return $this->database->lastInsertId();
    }
}
?>
