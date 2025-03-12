<?php
session_start();
require 'db.php';
require 'currency_functions.php'; // Include currency functions

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    die("You must be logged in to send currency.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sender_id = $_SESSION['user_id'];
    $recipient_id = $_POST['recipient_id'];
    $amount = $_POST['amount'];
    $description = $_POST['description'];

    // Validate input
    if ($amount <= 0) {
        die("Amount must be greater than zero.");
    }

    // Process transfer
    $result = transferCurrency($sender_id, $recipient_id, $amount, $description);
    echo $result;
} else {
    die("Invalid request method.");
}
?>