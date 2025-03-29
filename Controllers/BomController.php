<?php
namespace MyApp\Controllers;

require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php'; // Corrected path to config.php
require_once BASE_PATH . '/Models/Database.php';
require_once BASE_PATH . '/Models/Bom.php';

use MyApp\Models\Database;
use MyApp\Models\Bom;

class BomController {
    private $database;
    private $bom;

    public function __construct(Database $database) {
        $this->database = $database;
        $this->bom = new Bom($database);
    }

    public function addBom($project_name, $material_names, $lengths, $widths, $thicknesses, $quantities) {
        if (empty($project_name)) {
            echo "<script>alert('Project Name is required.'); window.history.back();</script>";
            exit();
        }

        foreach ($material_names as $index => $material_name) {
            $length = $lengths[$index];
            $width = $widths[$index];
            $thickness = $thicknesses[$index];
            $quantity = $quantities[$index];

            $this->bom->addBom($project_name, $material_name, $length, $width, $thickness, $quantity);
        }

        header("Location: /Views/estimate/add_estimate.php?project_name=$project_name");
        exit();
    }

    public function getBomByProjectName($project_name) {
        return $this->bom->getBomByProjectName($project_name);
    }

    public function getProjectAndCustomerDetails($project_name) {
        return $this->bom->getProjectAndCustomerDetails($project_name);
    }
}
?>
