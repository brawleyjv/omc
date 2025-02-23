<?php

namespace MyApp\Controllers;

use MyApp\Models\Database;

class EstimateController {
    private $database;

    public function __construct(Database $database) {
        $this->database = $database;
    }

    // Add your methods here
}
?>
