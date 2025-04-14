<?php
require_once __DIR__ . '/../../config.php'; // Updated to use __DIR__
require_once BASE_PATH . 'Models/Database.php'; // Include the Database class

use MyApp\Models\Database;

$customers = []; // Initialize customers array
try {
    $db = new Database(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $connection = $db->getConnection();

    if ($connection) { // Ensure connection is valid
        $query = "SELECT * FROM customers ORDER BY name ASC";
        $stmt = $connection->query($query);
        $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        throw new Exception("Database connection is null.");
    }
} catch (Exception $e) {
    error_log("Error fetching customers: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>List Customers</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>styles.css"> <!-- Ensure styles.css is included -->
    <style>
        table {
            border-collapse: collapse; /* Ensure borders do not double */
            width: 100%; /* Full width table */
        }
        th, td {
            border: 1px solid #ddd; /* Add borders to cells */
            padding: 8px; /* Add padding for better readability */
            text-align: left; /* Align text to the left */
        }
        th {
            background-color: #f2f2f2; /* Light gray background for header */
        }
        tr:nth-child(even) {
            background-color: #f9f9f9; /* Alternate row background color */
        }
        tr:hover {
            background-color: #f1f1f1; /* Highlight row on hover */
        }
        .action-cell {
            display: flex; /* Use flexbox for layout */
            gap: 10px; /* Add spacing between buttons */
            justify-content: center; /* Center buttons horizontally */
        }
        .btn {
            padding: 5px 10px; /* Adjust padding for buttons */
            font-size: 14px; /* Adjust font size */
        }
    </style>
</head>
<body>
    <?php include BASE_PATH . '/Views/header.php'; ?> <!-- Include header -->
    <div class="container">
        <h1 class="title">List of Customers</h1>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Project</th>
                    <th>Address</th>
                    <th>City</th>
                    <th>State</th>
                    <th>Zip</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Notes</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($customers)): ?>
                    <?php foreach ($customers as $customer): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($customer['id'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($customer['name'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($customer['Project'] ?? ''); ?></td> <!-- Handle NULL values -->
                            <td><?php echo htmlspecialchars($customer['address'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($customer['city'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($customer['state'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($customer['zip'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($customer['phone'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($customer['email'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($customer['notes'] ?? ''); ?></td>
                            <td class="action-cell">
                                <a href="<?php echo BASE_URL; ?>Views/customers/edit_customer.php?id=<?php echo htmlspecialchars($customer['id'] ?? ''); ?>" class="btn styled-btn">Edit</a>
                                <form action="<?php echo BASE_URL; ?>public/customers/delete_customer.php" method="post" onsubmit="return confirm('Are you sure you want to delete this customer?');" style="display:inline;">
                                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($customer['id'] ?? ''); ?>">
                                    <button type="submit" class="btn styled-btn red">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="11">No customers found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
