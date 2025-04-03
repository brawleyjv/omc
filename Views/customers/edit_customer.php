<?php
require_once __DIR__ . '/../../config.php'; // Updated to use __DIR__
require_once BASE_PATH . 'Models/Database.php'; // Include the Database class

use MyApp\Models\Database;

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: " . BASE_URL . "Views/customers/search_customer.php?error=Customer ID is required");
    exit();
}

$customerId = intval($_GET['id']);
$customer = null;

try {
    $db = new Database(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $connection = $db->getConnection();

    if ($connection instanceof PDO) { // Ensure connection is a valid PDO object
        $query = "SELECT * FROM customers WHERE customer_id = ?";
        $stmt = $connection->prepare($query);
        $stmt->execute([$customerId]);
        $customer = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$customer) {
            header("Location: " . BASE_URL . "Views/customers/search_customer.php?error=Customer not found");
            exit();
        }
    } else {
        throw new Exception("Database connection is invalid.");
    }
} catch (Exception $e) {
    error_log("Error fetching customer: " . $e->getMessage());
    header("Location: " . BASE_URL . "Views/customers/search_customer.php?error=Failed to fetch customer");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Customer</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>styles.css"> <!-- Include styles.css -->
</head>
<body>
    <?php include BASE_PATH . '/Views/header.php'; ?> <!-- Include header -->
    <div class="container">
        <h1 class="title">Edit Customer</h1>
        <form action="<?php echo BASE_URL; ?>public/customers/update_customer.php" method="post"> <!-- Use BASE_URL -->
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($customer['customer_id'] ?? ''); ?>">

            <label for="name">Name:</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($customer['name'] ?? ''); ?>" maxlength="100" required>

            <label for="project_name">Project Name:</label>
            <input type="text" id="project_name" name="project_name" value="<?php echo htmlspecialchars($customer['Project'] ?? ''); ?>" maxlength="100">

            <fieldset>
                <legend>Address</legend>
                <label for="address">Street Address:</label>
                <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($customer['address'] ?? ''); ?>" maxlength="200">

                <label for="city">City:</label>
                <input type="text" id="city" name="city" value="<?php echo htmlspecialchars($customer['city'] ?? ''); ?>" maxlength="100">

                <div class="form-row">
                    <label for="state">State:</label>
                    <input type="text" id="state" name="state" value="<?php echo htmlspecialchars($customer['state'] ?? ''); ?>" maxlength="50">

                    <label for="zip">Zip Code:</label>
                    <input type="text" id="zip" name="zip" value="<?php echo htmlspecialchars($customer['zip'] ?? ''); ?>" maxlength="10" class="zip">
                </div>
            </fieldset>

            <label for="phone">Phone Number:</label>
            <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($customer['phone'] ?? ''); ?>" maxlength="15">

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($customer['email'] ?? ''); ?>" maxlength="100">

            <label for="notes">Notes:</label>
            <textarea id="notes" name="notes" maxlength="500"><?php echo htmlspecialchars($customer['notes'] ?? ''); ?></textarea>

            <div class="button-container">
                <button type="submit" class="btn styled-btn">Update Customer</button>
                <a href="<?php echo BASE_URL; ?>Views/customers/search_customer.php" class="btn styled-btn red">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>
