<?php
session_start();
require 'db.php'; // Include database connection

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    die("You must be logged in to upload items.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $slot = $_POST['slot'];
    $image = $_FILES['image'];

    // Basic validation
    if (empty($name) || empty($slot) || empty($image)) {
        die("All fields are required.");
    }

    // Validate the slot
    $valid_slots = ['hat', 'top', 'pants', 'shoes', 'accessory', 'item'];
    if (!in_array($slot, $valid_slots)) {
        die("Invalid slot selected.");
    }

    // Handle file upload
    $upload_dir = 'uploads/items/';
    $image_name = basename($image['name']);
    $target_file = $upload_dir . $image_name;

    // Check if image file is a valid image
    $check = getimagesize($image['tmp_name']);
    if ($check === false) {
        die("File is not an image.");
    }

    // Move the uploaded file to the target directory
    if (!move_uploaded_file($image['tmp_name'], $target_file)) {
        die("Sorry, there was an error uploading your file.");
    }

    // Save item details to the database
    $stmt = $pdo->prepare("INSERT INTO items (name, type, image_path) VALUES (?, ?, ?)");
    $stmt->execute([$name, $slot, $target_file]);

    echo "Item uploaded successfully.";
} else {
    die("Invalid request method.");
}
?>