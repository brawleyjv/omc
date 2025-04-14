<?php
require_once realpath(dirname(__FILE__) . '/../../../config.php'); // Correct relative path to config.php
require_once BASE_PATH . '/Views/header.php'; // Include header
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>styles.css"> <!-- Corrected path -->
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: center;
        }
        th {
            background-color: #f4f4f4;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        .btn.styled-btn {
            padding: 5px 10px;
            font-size: 14px;
            border: none;
            border-radius: 5px;
            background-color: #007BFF;
            color: white;
            text-decoration: none;
            cursor: pointer;
        }
        .btn.styled-btn.red {
            background-color: #DC3545;
        }
        .btn.styled-btn.red:hover {
            background-color: #c82333;
        }
        .btn.styled-btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Search Results</h1>
        <?php if (!empty($vendors)): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Vendor</th>
                        <th>Phone</th>
                        <th>Web Address</th>
                        <th>Mailing Address</th>
                        <th>Email Address</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($vendors as $vendor): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($vendor['id'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($vendor['Vendor'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($vendor['phone'] ?? 'N/A'); ?></td> <!-- Default to 'N/A' if missing -->
                            <td><?php echo htmlspecialchars($vendor['web_address'] ?? 'N/A'); ?></td> <!-- Default to 'N/A' if missing -->
                            <td><?php echo htmlspecialchars($vendor['mailing_address'] ?? 'N/A'); ?></td> <!-- Default to 'N/A' if missing -->
                            <td><?php echo htmlspecialchars($vendor['email_address'] ?? 'N/A'); ?></td> <!-- Default to 'N/A' if missing -->
                            <td>
                                <a href="<?php echo BASE_URL; ?>Views/vendors/edit_vendor.php?vendor_id=<?php echo htmlspecialchars($vendor['id']); ?>" class="btn styled-btn">Edit</a>
                                <form action="<?php echo BASE_URL; ?>public/Vendors/delete_vendor.php" method="post" onsubmit="return confirm('Are you sure you want to delete this vendor?');" style="display:inline;">
                                    <input type="hidden" name="vendor_id" value="<?php echo htmlspecialchars($vendor['id']); ?>">
                                    <input type="submit" class="btn styled-btn red" value="Delete">
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No vendors found matching your search.</p>
        <?php endif; ?>
        <a href="<?php echo BASE_URL; ?>Views/vendors/index.php" class="btn styled-btn">Back to Vendors</a>
    </div>
</body>
</html>
