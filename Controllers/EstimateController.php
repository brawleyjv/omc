<?php

namespace MyApp\Controllers;

use MyApp\Models\Database;
use MyApp\Models\Bom;
use MyApp\Models\Project;

class EstimateController {
    private $database;
    private $bom;
    private $project;

    public function __construct(Database $database) {
        $this->database = $database;
        $this->bom = new Bom($database);
        $this->project = new Project($database);
    }

    public function getProjectById($project_id) {
        return $this->project->getProjectById($project_id);
    }

    public function getBomByProjectId($project_id) {
        return $this->bom->getBomByProjectId($project_id);
    }
}
?>
