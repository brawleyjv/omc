<?php
namespace MyApp\Models;

use PDO;
use PDOException;

class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $conn;

    public function __construct($host, $db_name, $username, $password) {
        $this->host = $host;
        $this->db_name = $db_name;
        $this->username = $username;
        $this->password = $password;
    }

    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }

        return $this->conn;
    }

    public function insertProject(Project $project) {
        $this->getConnection(); // Ensure the connection is established
        $query = "INSERT INTO projects (project_name, design_date, customer_name, laser_time, router_time, labor_hours, project_description, due_date, file_upload, image_upload) 
                  VALUES (:project_name, :design_date, :customer_name, :laser_time, :router_time, :labor_hours, :project_description, :due_date, :file_upload, :image_upload)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':project_name', $project->getProjectName());
        $stmt->bindValue(':design_date', $project->getDesignDate());
        $stmt->bindValue(':customer_name', $project->getCustomerName());
        $stmt->bindValue(':laser_time', $project->getLaserTime());
        $stmt->bindValue(':router_time', $project->getRouterTime());
        $stmt->bindValue(':labor_hours', $project->getLaborHours());
        $stmt->bindValue(':project_description', $project->getProjectDescription());
        $stmt->bindValue(':due_date', $project->getDueDate());
        $stmt->bindValue(':file_upload', json_encode($project->getFileUpload())); // Handle multiple file paths
        $stmt->bindValue(':image_upload', json_encode($project->getImageUpload())); // Handle multiple image paths
        $stmt->execute();

        return $this->conn->lastInsertId();
    }
}
?>