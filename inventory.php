<?php
session_start();
require 'db.php'; // Include database connection

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    die("You must be logged in to view your inventory.");
}

$user_id = $_SESSION['user_id'];

// Fetch the user's currency balance
$stmt = $pdo->prepare("SELECT currency_balance FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch the user's inventory
$stmt = $pdo->prepare("SELECT items.id, items.name, items.type, items.image_path FROM user_inventory JOIN items ON user_inventory.item_id = items.id WHERE user_inventory.user_id = ?");
$stmt->execute([$user_id]);
$inventory_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Inventory</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
    <h2>My Inventory</h2>
    <p>Currency Balance: <?php echo htmlspecialchars($user['currency_balance']); ?></p>
    <?php if (empty($inventory_items)): ?>
        <p>You have no items in your inventory.</p>
    <?php else: ?>
        <div class="inventory">
            <?php foreach ($inventory_items as $item): ?>
                <div class="inventory-item">
                    <img src="<?php echo htmlspecialchars($item['image_path']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="inventory-item-image">
                    <p><?php echo htmlspecialchars($item['name']); ?></p>
                    <form action="equip_item.php" method="post">
                        <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                        <input type="hidden" name="slot" value="<?php echo $item['type']; ?>">
                        <button type="submit">Equip</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</body>
</html>