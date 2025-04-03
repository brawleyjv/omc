<?php
namespace MyApp\Controllers;

use PDO; // Import the PDO class
require_once realpath(dirname(__FILE__) . '/../config.php');
require_once BASE_PATH . '/Models/Bom.php';

use MyApp\Models\Bom;

class BomController {
    private PDO $database;
    private Bom $bom;

    public function __construct(PDO $database) { // Accept a PDO object
        $this->database = $database;
        $this->bom = new Bom($database); // Pass the PDO object to the Bom model
    }

    public function addBom($project_id, $material_names, $lengths, $widths, $thicknesses, $quantities) {
        if (empty($project_id)) {
            echo "<script>alert('Project ID is required.'); window.history.back();</script>";
            exit();
        }

        foreach ($material_names as $index => $material_name) {
            $length = $lengths[$index];
            $width = $widths[$index];
            $thickness = $thicknesses[$index];
            $quantity = $quantities[$index];

            $this->bom->addBom($project_id, $material_name, $length, $width, $thickness, $quantity);
        }

        header("Location: " . BASE_URL . "Views/estimate/add_estimate.php?project_id=$project_id");
        exit();
    }

    public function addBomForProject($projectId, $materials) {
        foreach ($materials as $material) {
            $this->bom->addBom(
                $projectId,
                $material['material_name'],
                $material['length'],
                $material['width'],
                $material['thickness'],
                $material['quantity']
            );
        }
    }

    public function getBomByProjectName($project_name) {
        return $this->bom->getBomByProjectName($project_name);
    }

    public function getProjectAndCustomerDetails($project_name) {
        return $this->bom->getProjectAndCustomerDetails($project_name);
    }
}
?>
