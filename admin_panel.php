<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if the user is not logged in
    header("Location: login.php");
    exit;
}

// Check if the user has the required role
if ($_SESSION['role'] !== 'staff' && $_SESSION['role'] !== 'admin') {
    // Redirect to an error page or display an error message
    header("Location: home.php");
    exit;
}

// Define a constant to prevent direct access to included files
define('ALLOW_INCLUDE', true);


require 'db.php'; // Include database connection
require 'ban_functions.php'; // Include ban functions

// Check if the form to ban/unban a user was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'];
    $action = $_POST['action'];

    if ($action == 'ban') {
        if (ban_user($user_id)) {
            echo "User $user_id has been banned.";
        } else {
            echo "Failed to ban user $user_id. User ID may not exist.";
        }
    } elseif ($action == 'unban') {
        if (unban_user($user_id)) {
            echo "User $user_id has been unbanned.";
        } else {
            echo "Failed to unban user $user_id. User ID may not exist.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Panel</title>
</head>
<body>
    <h2>Ban/Unban User</h2>
    <form method="post" action="">
        <input type="number" name="user_id" placeholder="User ID" required><br>
        <input type="radio" name="action" value="ban" required> Ban<br>
        <input type="radio" name="action" value="unban" required> Unban<br>
        <button type="submit">Submit</button>
    </form>
</body>
</html>