<?php
session_start();
require 'db.php'; // Include database connection

if (!isset($_SESSION['user_id'])) {
    die("You must be logged in to see your avatar.");
}

$user_id = $_SESSION['user_id'];

// Get equipped items
$stmt = $pdo->prepare("SELECT items.image_path, user_equipped_items.slot FROM user_equipped_items JOIN items ON user_equipped_items.item_id = items.id WHERE user_equipped_items.user_id = ?");
$stmt->execute([$user_id]);
$equipped_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Layers for the avatar
$layers = [
    'base' => 'images/base.png',
    'hat' => '',
    'top' => '',
    'pants' => '',
    'shoes' => '',
    'accessory' => '',
    'item' => ''
];

foreach ($equipped_items as $equipped_item) {
    $layers[$equipped_item['slot']] = $equipped_item['image_path'];
}

// Display the avatar
echo '<div class="avatar">';
foreach ($layers as $layer) {
    if ($layer) {
        echo '<img src="' . $layer . '" class="avatar-layer">';
    }
}
echo '</div>';
?>