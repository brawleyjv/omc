<?php
namespace Models;

require_once $_SERVER['DOCUMENT_ROOT'] . '/omc/config.php'; // Corrected path to config.php

class Settings {
    private $conn;

    public function __construct() {
        // Use constants directly instead of Globals\Config
        $this->conn = new \mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
    }

    public function getSettings() {
        $sql = "SELECT company_name, company_slogan FROM settings LIMIT 1";
        $result = $this->conn->query($sql);
        return $result->fetch_assoc();
    }

    public function saveSettings($company_name, $company_slogan) {
        // Check if there is an existing record in the settings table
        $check_record_query = "SELECT * FROM settings LIMIT 1";
        $record_exists = $this->conn->query($check_record_query);

        if ($record_exists->num_rows > 0) {
            // Update the existing record
            $update_query = "UPDATE settings SET company_name=?, company_slogan=? WHERE id=1";
            $stmt = $this->conn->prepare($update_query);
            $stmt->bind_param("ss", $company_name, $company_slogan);
            $stmt->execute();
            $stmt->close();
        } else {
            // Insert a new record
            $insert_query = "INSERT INTO settings (company_name, company_slogan) VALUES (?, ?)";
            $stmt = $this->conn->prepare($insert_query);
            $stmt->bind_param("ss", $company_name, $company_slogan);
            $stmt->execute();
            $stmt->close();
        }
    }

    // Other methods as needed
}
?>
