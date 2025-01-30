<?php
include '../../controllers/VendorController.php';

$vendorController = new VendorController();
$vendor = null;
$updateSuccess = null;

if (isset($_GET['vendor_id'])) {
    $vendorId = $_GET['vendor_id'];
    error_log("Received vendor_id from GET: $vendorId"); // Debugging: Log the received vendor ID from GET
    $vendor = $vendorController->getVendorById($vendorId);
    error_log("Vendor Data: " . print_r($vendor, true)); // Debugging: Log the vendor data
} else {
    error_log("No vendor_id received from GET"); // Debugging: Log if no vendor ID is received
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $vendorId = $_POST['vendor_id'];
    error_log("Received vendor_id from POST: $vendorId"); // Debugging: Log the received vendor ID from POST
    $vendorName = $_POST['vendor'];
    $vendorPhone = $_POST['phone'];
    $vendorWebAddress = $_POST['web_address'];
    $vendorMailingAddress = $_POST['mailing_address'];
    $vendorEmailAddress = $_POST['email_address'];

    $result = $vendorController->updateVendor($vendorId, $vendorName, $vendorPhone, $vendorWebAddress, $vendorMailingAddress, $vendorEmailAddress);
    error_log("Update result: " . ($result ? 'success' : 'failure')); // Debugging: Log the result of the update

    $updateSuccess = $result ? 'success' : 'failure';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Vendor</title>
    <link rel="stylesheet" type="text/css" href="../../public/css/styles.css">
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const updateSuccess = "<?php echo $updateSuccess; ?>";
            if (updateSuccess === 'success') {
                alert('Vendor updated successfully.');
                window.location.href = 'list_vendors.php';
            } else if (updateSuccess === 'failure') {
                alert('Failed to update vendor.');
            }
        });
    </script>
</head>
<body>
    <?php include '../header.php'; ?>
    <h1>Edit Vendor</h1>
    <?php if (isset($vendor) && $vendor): ?>
        <form action="edit_vendor.php" method="post">
            <label for="vendor_id">Vendor ID:</label>
            <input type="text" id="vendor_id" value="<?php echo htmlspecialchars($vendor['id'] ?? ''); ?>" readonly><br>
            <input type="hidden" name="vendor_id" value="<?php echo htmlspecialchars($vendor['id'] ?? ''); ?>">
            <label for="vendor">Name:</label>
            <input type="text" id="vendor" name="vendor" value="<?php echo htmlspecialchars($vendor['Vendor'] ?? ''); ?>" required><br>
            <label for="phone">Phone:</label>
            <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($vendor['phone'] ?? ''); ?>"><br>
            <label for="web_address">Web Address:</label>
            <input type="text" id="web_address" name="web_address" value="<?php echo htmlspecialchars($vendor['web_address'] ?? ''); ?>"><br>
            <label for="mailing_address">Mailing Address:</label>
            <input type="text" id="mailing_address" name="mailing_address" value="<?php echo htmlspecialchars($vendor['mailing_address'] ?? ''); ?>"><br>
            <label for="email_address">Email Address:</label>
            <input type="email" id="email_address" name="email_address" value="<?php echo htmlspecialchars($vendor['email_address'] ?? ''); ?>"><br>
            <button type="submit" class="btn styled-btn white">Update Vendor</button>
        </form>
    <?php else: ?>
        <p>Vendor not found.</p>
    <?php endif; ?>
</body>
</html>
