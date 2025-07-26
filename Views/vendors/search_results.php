<?php
require_once realpath(dirname(__FILE__) . '/../../config.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>styles-modern.css">
    <title>Vendor Search Results</title>
</head>
<body>
    <?php include BASE_PATH . '/Views/header.php'; ?>

    <div class="main-content">
        <div class="page-header">
            <div class="header-content">
                <h1>Vendor Search Results</h1>
                <div class="header-actions">
                    <a href="<?php echo BASE_URL; ?>Views/vendors/search_vendors.php" class="btn btn-secondary">Back to Search</a>
                    <a href="<?php echo BASE_URL; ?>Views/vendors/add_vendor.php" class="btn btn-primary">Add New Vendor</a>
                </div>
            </div>
        </div>

        <?php if (!empty($vendors)): ?>
            <div class="card">
                <div class="card-header">
                    <h2>Search Results</h2>
                    <p class="text-muted">Found <?php echo count($vendors); ?> vendors</p>
                </div>
                <div class="table-container">
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th>Vendor Name</th>
                                <th>Contact Info</th>
                                <th>Address</th>
                                <th>Website</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($vendors as $vendor): ?>
                                <tr>
                                    <td>
                                        <div class="font-semibold"><?php echo htmlspecialchars($vendor['Vendor'] ?? 'N/A'); ?></div>
                                        <div class="text-sm text-muted">ID: <?php echo htmlspecialchars($vendor['id'] ?? ''); ?></div>
                                    </td>
                                    <td>
                                        <div class="text-sm">
                                            <?php if (!empty($vendor['email_address']) && $vendor['email_address'] !== 'N/A'): ?>
                                                <div>📧 <?php echo htmlspecialchars($vendor['email_address']); ?></div>
                                            <?php endif; ?>
                                            <?php if (!empty($vendor['phone']) && $vendor['phone'] !== 'N/A'): ?>
                                                <div>📞 <?php echo htmlspecialchars($vendor['phone']); ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="text-sm">
                                            <?php if (!empty($vendor['mailing_address']) && $vendor['mailing_address'] !== 'N/A'): ?>
                                                <?php echo htmlspecialchars($vendor['mailing_address']); ?>
                                            <?php else: ?>
                                                <span class="text-muted">No address</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if (!empty($vendor['web_address']) && $vendor['web_address'] !== 'N/A'): ?>
                                            <a href="<?php echo htmlspecialchars($vendor['web_address']); ?>" 
                                               target="_blank" 
                                               class="text-primary">Visit Website</a>
                                        <?php else: ?>
                                            <span class="text-muted">No website</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="<?php echo BASE_URL; ?>Views/vendors/edit_vendor.php?vendor_id=<?php echo htmlspecialchars($vendor['id']); ?>" 
                                               class="btn btn-sm btn-secondary">Edit</a>
                                            <button class="btn btn-sm btn-danger" 
                                                    onclick="if(confirm('Are you sure you want to delete this vendor?')) { var form = document.createElement('form'); form.method = 'post'; form.action = '<?php echo BASE_URL; ?>public/Vendors/delete_vendor.php'; var input = document.createElement('input'); input.type = 'hidden'; input.name = 'vendor_id'; input.value = '<?php echo htmlspecialchars($vendor['id']); ?>'; form.appendChild(input); document.body.appendChild(form); form.submit(); }">
                                                Delete
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php else: ?>
            <div class="card">
                <div class="card-body text-center">
                    <div class="empty-state">
                        <h3>No Vendors Found</h3>
                        <p class="text-muted">No vendors match your search criteria. Try a different search term.</p>
                        <a href="<?php echo BASE_URL; ?>Views/vendors/add_vendor.php" class="btn btn-primary">Add New Vendor</a>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body text-center">
                <a href="<?php echo BASE_URL; ?>Views/vendors/index.php" class="btn btn-secondary">Back to Vendors</a>
            </div>
        </div>
    </div>
</body>
</html>
