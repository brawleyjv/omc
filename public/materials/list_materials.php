<?php
// filepath: /c:/xampp/htdocs/OMC/public/materials/list_materials.php

require_once __DIR__ . '/../../Globals/Config.php';
require_once __DIR__ . '/../../Models/Database.php';
require_once __DIR__ . '/../../Models/Material.php';
require_once __DIR__ . '/../../Controllers/MaterialController.php';
use MyApp\Controllers\MaterialController;
use MyApp\Models\Database;

$database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS);
$materialsController = new MaterialController($database);
$materials = $materialsController->getAllMaterials();

include __DIR__ . '/../../views/materials/list_materials.php';
?>