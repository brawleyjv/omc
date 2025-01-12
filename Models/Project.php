<?php
// filepath: /c:/xampp/htdocs/OMC/Models/Project.php

namespace MyApp\Models;

class Project {
    public $project_name;
    public $design_date;
    public $customer_name;
    public $laser_time;
    public $router_time;
    public $labor_hours;
    public $project_description;
    public $due_date;
    public $file_upload;
    public $image_upload;
    public $design_file;

    public function __construct($project_name, $design_date, $customer_name, $laser_time, $router_time, $labor_hours, $project_description, $due_date, $file_upload, $image_upload, $design_file) {
        $this->project_name = $project_name;
        $this->design_date = $design_date;
        $this->customer_name = $customer_name;
        $this->laser_time = $laser_time;
        $this->router_time = $router_time;
        $this->labor_hours = $labor_hours;
        $this->project_description = $project_description;
        $this->due_date = $due_date;
        $this->file_upload = $file_upload;
        $this->image_upload = $image_upload;
        $this->design_file = $design_file;
    }

    public function getProjectName() {
        return $this->project_name;
    }

    public function getDesignDate() {
        return $this->design_date;
    }

    public function getCustomerName() {
        return $this->customer_name;
    }

    public function getLaserTime() {
        return $this->laser_time;
    }

    public function getRouterTime() {
        return $this->router_time;
    }

    public function getLaborHours() {
        return $this->labor_hours;
    }

    public function getProjectDescription() {
        return $this->project_description;
    }

    public function getDueDate() {
        return $this->due_date;
    }

    public function getFileUploads() {
        return $this->file_upload;
    }

    public function getImageUploads() {
        return $this->image_upload;
    }

    // Add missing methods
    public function getFileUpload() {
        return $this->file_upload;
    }

    public function getImageUpload() {
        return $this->image_upload;
    }
}
?>