<?php
require_once realpath(dirname(__FILE__, 3) . '/config.php'); // Corrected path to config.php
require_once BASE_PATH . '/public/Customers/search_customer.php'; // Include the PHP logic
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Customers</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>styles.css"> <!-- Include your CSS -->
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #007BFF; /* Add borders to table cells */
        }
        th, td {
            padding: 10px;
            text-align: center; /* Center the content in each cell */
        }
        th {
            background-color: #f2f2f2;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        .btn.styled-btn.red {
            background-color: #DC3545; /* Red background */
            color: white; /* White text */
            padding: 5px 10px;
            font-size: 14px;
            border: none;
        }
        .btn.styled-btn.red:hover {
            background-color: #c82333; /* Darker red on hover */
        }
        .btn.styled-btn.white {
            background-color: white; /* White background */
            color: #007BFF; /* Blue text */
            padding: 5px 10px;
            font-size: 14px;
            border: 2px solid #007BFF; /* Blue border */
        }
        .btn.styled-btn.white:hover {
            background-color: #f2f2f2; /* Light gray on hover */
        }
    </style>
</head>
<body>
    <?php include BASE_PATH . '/Views/header.php'; ?> <!-- Include the header -->
    <h1>Search Customers</h1>
    <form method="get" action="">
        <input type="text" name="query" placeholder="Search by name or email" value="<?php echo htmlspecialchars($query); ?>">
        <button type="submit">Search</button>
    </form>

    <?php if (!empty($customers)): ?>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>City</th>
                    <th>State</th>
                    <th>Zip</th>
                    <th>Actions</th> <!-- Add Actions column -->
                </tr>
            </thead>
            <tbody>
                <?php foreach ($customers as $customer): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($customer['name']); ?></td>
                        <td><?php echo htmlspecialchars($customer['email']); ?></td>
                        <td><?php echo htmlspecialchars($customer['phone']); ?></td>
                        <td><?php echo htmlspecialchars($customer['city']); ?></td>
                        <td><?php echo htmlspecialchars($customer['state']); ?></td>
                        <td><?php echo htmlspecialchars($customer['zip']); ?></td>
                        <td>
                            <!-- Edit button -->
                            <a href="<?php echo BASE_URL; ?>public/Customers/update_customer.php?id=<?php echo urlencode($customer['customer_id']); ?>" class="btn styled-btn white">Edit</a>
                            <!-- Delete button -->
                            <form action="<?php echo BASE_URL; ?>public/Customers/delete_customer.php" method="post" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this customer?');">
                                <input type="hidden" name="id" value="<?php echo htmlspecialchars($customer['customer_id']); ?>">
                                <input type="submit" class="btn styled-btn red" value="Delete">
                            </form>
                            <!-- View Projects button -->
                            <a href="<?php echo BASE_URL; ?>Views/projects/list_projects.php?customer_id=<?php echo urlencode($customer['customer_id']); ?>" class="btn styled-btn white">View Projects</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && !empty($query)): ?>
        <p>No customers found for "<?php echo htmlspecialchars($query); ?>"</p>
    <?php endif; ?>
</body>
</html>
