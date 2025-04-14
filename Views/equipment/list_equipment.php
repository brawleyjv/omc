<?php
require_once __DIR__ . '/../../config.php'; // Updated to use __DIR__
require_once BASE_PATH . 'Models/Database.php'; // Use BASE_PATH

use MyApp\Models\Database;

$database = new Database(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
$equipment = $database->query("SELECT * FROM equipment")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>List Equipment</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>styles.css"> <!-- Use BASE_URL -->
</head>
<body>
    <?php include BASE_PATH . 'Views/header.php'; ?> <!-- Use BASE_PATH -->
    <div class="container">
        <h1 class="title">List of Equipment</h1>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Equipment Name</th>
                    <th>Type</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($equipment as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['id']); ?></td>
                        <td><?php echo htmlspecialchars($item['name']); ?></td>
                        <td><?php echo htmlspecialchars($item['type']); ?></td>
                        <td>
                            <a href="<?php echo BASE_URL; ?>Views/equipment/edit_equipment.php?equipment_id=<?php echo $item['id']; ?>" class="btn styled-btn">Edit</a> <!-- Use BASE_URL -->
                            <form action="<?php echo BASE_URL; ?>public/equipment/delete_equipment.php" method="post" onsubmit="return confirm('Are you sure you want to delete this equipment?');" style="display:inline;"> <!-- Use BASE_URL -->
                                <input type="hidden" name="equipment_id" value="<?php echo $item['id']; ?>">
                                <input type="submit" class="btn styled-btn red" value="Delete">
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
