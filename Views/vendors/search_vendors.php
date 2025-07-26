<?php
require_once __DIR__ . '/../../config.php';
require_once BASE_PATH . '/Models/VendorModel.php';

// Initialize search results
$vendors = [];
$searchQuery = '';
$hasSearched = false;

// Handle search form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search_query'])) {
    $searchQuery = trim($_POST['search_query']);
    $hasSearched = true;
    
    if (!empty($searchQuery)) {
        try {
            $vendorModel = new VendorModel();
            $vendors = $vendorModel->searchVendors($searchQuery);
        } catch (Exception $e) {
            $errorMessage = "Error searching vendors: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Vendors - OMC</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>styles-modern.css">
</head>
<body>
    <!-- Modern Header -->
    <header class="header">
        <div class="header-content">
            <div class="header-brand">
                <div class="header-brand-text">
                    <h1>Search Vendors</h1>
                    <p>Find vendors by name, company, email, or phone</p>
                </div>
            </div>
            <nav class="header-nav">
                <a href="<?php echo BASE_URL; ?>Views/main.php" class="nav-link">Dashboard</a>
                <a href="<?php echo BASE_URL; ?>Views/vendors/list_vendors.php" class="nav-link">All Vendors</a>
                <a href="<?php echo BASE_URL; ?>Views/vendors/add_vendor.php" class="nav-link">Add Vendor</a>
            </nav>
        </div>
    </header>
    <!-- Main Content -->
    <main class="main-container">
        <!-- Page Header -->
        <div class="page-header">
            <div class="page-header-content">
                <h1 class="page-title">Vendor Search</h1>
                <div class="page-actions">
                    <a href="<?php echo BASE_URL; ?>Views/vendors/add_vendor.php" class="btn btn-primary">
                        <span class="icon">🏢</span>
                        Add New Vendor
                    </a>
                </div>
            </div>
        </div>

        <!-- Search Form -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Vendor Search</h2>
                <p class="card-subtitle">Search for vendors by name, company, email, or contact information</p>
            </div>
            <div class="card-body">
                <?php if (isset($errorMessage)): ?>
                    <div class="notification notification-error">
                        <?php echo htmlspecialchars($errorMessage); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="search_query" class="form-label">Search Query</label>
                            <input type="text" id="search_query" name="search_query" class="form-control" 
                                   value="<?php echo htmlspecialchars($searchQuery); ?>"
                                   placeholder="Enter vendor name, company, email, or phone number...">
                            <small class="form-text">Search across all vendor fields including name, company, email, and contact information</small>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <span class="icon">🔍</span>
                            Search Vendors
                        </button>
                        <?php if ($hasSearched): ?>
                            <a href="<?php echo BASE_URL; ?>Views/vendors/search_vendors.php" class="btn btn-secondary">
                                <span class="icon">🔄</span>
                                Clear Search
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <!-- Search Results -->
        <?php if ($hasSearched): ?>
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Search Results</h2>
                    <p class="card-subtitle">
                        <?php if (empty($vendors)): ?>
                            No vendors found matching "<?php echo htmlspecialchars($searchQuery); ?>"
                        <?php else: ?>
                            Found <?php echo count($vendors); ?> vendor(s) matching "<?php echo htmlspecialchars($searchQuery); ?>"
                        <?php endif; ?>
                    </p>
                </div>
                <div class="card-body">
                    <?php if (empty($vendors)): ?>
                        <div class="notification notification-info">
                            <p><strong>No vendors found.</strong></p>
                            <p>Try adjusting your search terms or browse all vendors.</p>
                            <div class="mt-3">
                                <a href="<?php echo BASE_URL; ?>Views/vendors/list_vendors.php" class="btn btn-primary">
                                    <span class="icon">🏢</span>
                                    View All Vendors
                                </a>
                                <a href="<?php echo BASE_URL; ?>Views/vendors/add_vendor.php" class="btn btn-ghost">
                                    <span class="icon">➕</span>
                                    Add New Vendor
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="table-container">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Company</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Location</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($vendors as $vendor): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($vendor['name'] ?? ''); ?></strong>
                                            </td>
                                            <td><?php echo htmlspecialchars($vendor['company'] ?? 'N/A'); ?></td>
                                            <td>
                                                <?php if (!empty($vendor['email'])): ?>
                                                    <a href="mailto:<?php echo htmlspecialchars($vendor['email']); ?>" class="text-primary">
                                                        <?php echo htmlspecialchars($vendor['email']); ?>
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted">No email</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (!empty($vendor['phone'])): ?>
                                                    <a href="tel:<?php echo htmlspecialchars($vendor['phone']); ?>" class="text-primary">
                                                        <?php echo htmlspecialchars($vendor['phone']); ?>
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted">No phone</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php 
                                                $location = [];
                                                if (!empty($vendor['city'])) $location[] = $vendor['city'];
                                                if (!empty($vendor['state'])) $location[] = $vendor['state'];
                                                echo !empty($location) ? htmlspecialchars(implode(', ', $location)) : 'N/A';
                                                ?>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="<?php echo BASE_URL; ?>Views/vendors/view_vendor.php?id=<?php echo $vendor['id']; ?>" 
                                                       class="btn btn-ghost btn-sm" title="View Vendor">
                                                        <span class="icon">👁️</span>
                                                    </a>
                                                    <a href="<?php echo BASE_URL; ?>Views/vendors/edit_vendor.php?id=<?php echo $vendor['id']; ?>" 
                                                       class="btn btn-ghost btn-sm" title="Edit Vendor">
                                                        <span class="icon">✏️</span>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <script>
        // Auto-focus the search input when page loads
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('search_query');
            if (searchInput && !searchInput.value) {
                searchInput.focus();
            }
        });
    </script>
</body>
</html>
        }
        .button-container {
            display: flex;
            justify-content: space-between;
        }
        .btn.styled-btn {
            padding: 10px 20px;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            cursor: pointer;
        }
        .btn.styled-btn:hover {
            background-color: #0056b3;
        }
        .btn.close-btn {
            background-color: #DC3545;
        }
        .btn.close-btn:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>
    <?php include BASE_PATH . '/Views/header.php'; ?> <!-- Include header -->
    <div class="container">
        <h1>Search Vendors</h1>
        <form action="<?php echo BASE_URL; ?>public/Vendors/search_vendors.php" method="get">
            <div class="form-group">
                <label for="search_term">Vendor Name:</label>
                <input type="text" id="search_term" name="search_term" placeholder="Enter vendor name or partial name" required>
            </div>
            <div class="button-container">
                <button type="submit" class="btn styled-btn">Search</button>
                <a href="<?php echo BASE_URL; ?>Views/vendors/index.php" class="btn styled-btn close-btn">Close</a>
            </div>
        </form>
    </div>
</body>
</html>
