<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config.php';
require_once BASE_PATH . 'Models/Database.php';

use MyApp\Models\Database;

// Ensure user is authenticated
if (!isset($_SESSION['username'])) {
    http_response_code(403);
    exit('Unauthorized');
}

$database = new Database(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
$pdo = $database->getPdo();

// Get date range from query params
$start_date = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
$end_date = $_GET['end_date'] ?? date('Y-m-d');

// Set CSV headers
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=etsy_sales_' . $start_date . '_to_' . $end_date . '.csv');

// Output UTF-8 BOM for Excel compatibility
echo "\xEF\xBB\xBF";

// Create output stream
$output = fopen('php://output', 'w');

// Write CSV headers
fputcsv($output, [
    'Order Date',
    'Order ID',
    'Etsy Receipt ID',
    'Customer Name',
    'Items',
    'Subtotal',
    'Shipping',
    'Tax',
    'Grand Total',
    'Status',
    'Shipped Date',
    'City',
    'State',
    'Country'
]);

// Query orders
$query = "
    SELECT 
        order_date,
        id,
        etsy_receipt_id,
        buyer_name,
        items_count,
        subtotal,
        shipping_cost,
        tax_amount,
        grand_total,
        status,
        shipped_date,
        ship_city,
        ship_state,
        ship_country
    FROM etsy_orders
    WHERE order_date BETWEEN :start_date AND :end_date
    ORDER BY order_date DESC
";

$stmt = $pdo->prepare($query);
$stmt->execute(['start_date' => $start_date, 'end_date' => $end_date]);

// Write data rows
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    fputcsv($output, [
        date('Y-m-d H:i:s', strtotime($row['order_date'])),
        $row['id'],
        $row['etsy_receipt_id'],
        $row['buyer_name'],
        $row['items_count'],
        number_format($row['subtotal'], 2),
        number_format($row['shipping_cost'], 2),
        number_format($row['tax_amount'], 2),
        number_format($row['grand_total'], 2),
        $row['status'],
        $row['shipped_date'] ? date('Y-m-d', strtotime($row['shipped_date'])) : '',
        $row['ship_city'],
        $row['ship_state'],
        $row['ship_country']
    ]);
}

fclose($output);
exit();
?>
