<?php
namespace Models;

require_once realpath(dirname(__FILE__) . '/../config.php');

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
        $sql = "SELECT company_name, company_slogan, company_address, company_city, company_state, 
                       company_zip, company_phone, company_email, company_logo,
                       smtp_host, smtp_port, smtp_username, smtp_password, smtp_from_email, 
                       smtp_from_name, smtp_encryption FROM settings LIMIT 1";
        $result = $this->conn->query($sql);
        return $result->fetch_assoc();
    }

    public function saveSettings($company_name, $company_slogan, $company_address = '', $company_city = '', 
                                  $company_state = '', $company_zip = '', $company_phone = '', 
                                  $company_email = '', $company_logo = '', $smtp_host = '', $smtp_port = 587,
                                  $smtp_username = '', $smtp_password = '', $smtp_from_email = '',
                                  $smtp_from_name = '', $smtp_encryption = 'tls') {
        // Check if there is an existing record in the settings table
        $check_record_query = "SELECT * FROM settings LIMIT 1";
        $record_exists = $this->conn->query($check_record_query);

        if ($record_exists->num_rows > 0) {
            // Update the existing record
            $update_query = "UPDATE settings SET company_name=?, company_slogan=?, company_address=?, 
                            company_city=?, company_state=?, company_zip=?, company_phone=?, 
                            company_email=?, company_logo=?, smtp_host=?, smtp_port=?, smtp_username=?,
                            smtp_password=?, smtp_from_email=?, smtp_from_name=?, smtp_encryption=? WHERE id=1";
            $stmt = $this->conn->prepare($update_query);
            $stmt->bind_param("ssssssssssisssss", $company_name, $company_slogan, $company_address, 
                             $company_city, $company_state, $company_zip, $company_phone, 
                             $company_email, $company_logo, $smtp_host, $smtp_port, $smtp_username,
                             $smtp_password, $smtp_from_email, $smtp_from_name, $smtp_encryption);
            $stmt->execute();
            $stmt->close();
        } else {
            // Insert a new record
            $insert_query = "INSERT INTO settings (company_name, company_slogan, company_address, 
                            company_city, company_state, company_zip, company_phone, company_email, company_logo,
                            smtp_host, smtp_port, smtp_username, smtp_password, smtp_from_email, 
                            smtp_from_name, smtp_encryption) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->conn->prepare($insert_query);
            $stmt->bind_param("ssssssssssisssss", $company_name, $company_slogan, $company_address, 
                             $company_city, $company_state, $company_zip, $company_phone, 
                             $company_email, $company_logo, $smtp_host, $smtp_port, $smtp_username,
                             $smtp_password, $smtp_from_email, $smtp_from_name, $smtp_encryption);
            $stmt->execute();
            $stmt->close();
        }
    }

    // Other methods as needed
}
?>
