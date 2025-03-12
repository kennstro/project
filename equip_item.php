<?php
session_start();
require 'db.php'; // Include database connection

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    die("You must be logged in to equip items.");
}

// Check if item_id and slot are set in the POST request
if (!isset($_POST['item_id']) || !isset($_POST['slot'])) {
    die("Item ID and slot are required.");
}

$user_id = $_SESSION['user_id'];
$item_id = $_POST['item_id'];
$slot = $_POST['slot'];

// Check if the user owns the item
$stmt = $pdo->prepare("SELECT * FROM user_inventory WHERE user_id = ? AND item_id = ?");
$stmt->execute([$user_id, $item_id]);
$item = $stmt->fetch();

if (!$item) {
    die("You do not own this item.");
}

// Equip the item
$stmt = $pdo->prepare("REPLACE INTO user_equipped_items (user_id, item_id, slot) VALUES (?, ?, ?)");
$stmt->execute([$user_id, $item_id, $slot]);

echo "Item equipped successfully.";
?>