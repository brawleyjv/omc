<?php
require_once __DIR__ . '/../../Globals/Config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $project_id = $_POST['project_id'];
    $project_name = $_POST['project_name'];
    $customer_name = $_POST['customer_name'];
    $material_ids = $_POST['material_id'];
    $material_names = $_POST['material_name'];
    $lengths = $_POST['length'];
    $widths = $_POST['width'];
    $thicknesses = $_POST['thickness'];
    $quantities = $_POST['quantity'];

    $query_params = http_build_query([
        'project_id' => $project_id,
        'project_name' => $project_name,
        'customer_name' => $customer_name,
        'material_id' => $material_ids,
        'material_name' => $material_names,
        'length' => $lengths,
        'width' => $widths,
        'thickness' => $thicknesses,
        'quantity' => $quantities
    ]);

    header("Location: /OMC/public/Estimate/estimate.php?$query_params");
    exit();
}
?>
