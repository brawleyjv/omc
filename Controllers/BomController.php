<?php

namespace MyApp\Controllers;

use MyApp\Models\Database;
use MyApp\Models\Bom;

class BomController {
    private $database;
    private $bom;

    public function __construct(Database $database) {
        $this->database = $database;
        $this->bom = new Bom($database);
    }

    public function addBom($project_id, $project_name, $customer_name, $material_names, $lengths, $widths, $thicknesses, $quantities) {
        if (empty($project_id) || empty($project_name) || empty($customer_name)) {
            echo "<script>alert('Project ID, Project Name, and Customer Name are required.'); window.history.back();</script>";
            exit();
        }

        foreach ($material_names as $index => $material_name) {
            $length = $lengths[$index];
            $width = $widths[$index];
            $thickness = $thicknesses[$index];
            $quantity = $quantities[$index];

            $this->bom->addBom($project_id, $material_name, $length, $width, $thickness, $quantity);
        }

        header("Location: /OMC/Views/estimate.php?project_id=$project_id&project_name=" . urlencode($project_name) . "&customer_name=" . urlencode($customer_name));
        exit();
    }

    public function getBomByProjectId($project_id) {
        return $this->bom->getBomByProjectId($project_id);
    }
}
?>
