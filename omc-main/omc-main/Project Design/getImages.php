<?php
$category = $_GET['category'];
$directory = "images/$category";

if (!is_dir($directory)) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid category"]);
    exit;
}

$images = array_diff(scandir($directory), array('..', '.'));

if (empty($images)) {
    http_response_code(404);
    echo json_encode(["error" => "No images found"]);
    exit;
}

header('Content-Type: application/json');
echo json_encode(array_values($images));
?>
