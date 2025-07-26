<?php
require_once __DIR__ . '/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Table Styling Test - OMC</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>styles-modern.css">
</head>
<body>
    <div class="main-container">
        <h1 style="margin-bottom: 2rem;">Table Styling Test</h1>
        
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Sample User Table with Improved Padding</h2>
                <p class="card-subtitle">Testing the new table cell spacing and readability</p>
            </div>
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Name</th>
                            <th>User Type</th>
                            <th>Position</th>
                            <th>Date of Hire</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><span class="font-mono text-sm">1</span></td>
                            <td><div class="font-semibold">johnsmith</div></td>
                            <td>John Smith</td>
                            <td><span class="badge badge-danger">Admin</span></td>
                            <td>Manager</td>
                            <td>Jan 15, 2023</td>
                            <td>
                                <div class="btn-group">
                                    <button class="btn btn-sm btn-secondary">
                                        <span class="icon">✏️</span>
                                        Edit
                                    </button>
                                    <button class="btn btn-sm btn-danger">
                                        <span class="icon">🗑️</span>
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td><span class="font-mono text-sm">2</span></td>
                            <td><div class="font-semibold">janedoe</div></td>
                            <td>Jane Doe</td>
                            <td><span class="badge badge-warning">Manager</span></td>
                            <td>Supervisor</td>
                            <td>Mar 22, 2023</td>
                            <td>
                                <div class="btn-group">
                                    <button class="btn btn-sm btn-secondary">
                                        <span class="icon">✏️</span>
                                        Edit
                                    </button>
                                    <button class="btn btn-sm btn-danger">
                                        <span class="icon">🗑️</span>
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td><span class="font-mono text-sm">3</span></td>
                            <td><div class="font-semibold">mikejohnson</div></td>
                            <td>Mike Johnson</td>
                            <td><span class="badge badge-info">User</span></td>
                            <td>Employee</td>
                            <td>Jun 10, 2023</td>
                            <td>
                                <div class="btn-group">
                                    <button class="btn btn-sm btn-secondary">
                                        <span class="icon">✏️</span>
                                        Edit
                                    </button>
                                    <button class="btn btn-sm btn-danger">
                                        <span class="icon">🗑️</span>
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div style="margin-top: 2rem;">
            <h3>✅ Improvements Made:</h3>
            <ul>
                <li><strong>Fixed class name:</strong> Changed from <code>modern-table</code> to <code>table</code></li>
                <li><strong>Increased padding:</strong> Vertical padding increased from 16px to 24px</li>
                <li><strong>Improved hover effects:</strong> Added smooth transitions and subtle lift effect</li>
                <li><strong>Better button spacing:</strong> Improved gap between action buttons</li>
                <li><strong>Enhanced readability:</strong> More white space makes content easier to scan</li>
            </ul>
            
            <p><a href="Views/Users/list_users.php">→ View the actual users list with improved styling</a></p>
        </div>
    </div>
</body>
</html>
