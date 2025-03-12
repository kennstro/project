<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if the user is not logged in
    header("Location: login.php");
    exit;
}

// Get the username from the session if it exists
$username = isset($_SESSION['username']) ? $_SESSION['username'] : '';

// User is logged in, display the home page
?>
<!DOCTYPE html>
<html>
<head>
    <title>Home</title>
</head>
<body>
    <h2>Welcome to the Home Page</h2>
    <p>Hello, <?php echo htmlspecialchars($username); ?>! You are logged in.</p>
    <a href="logout.php">Logout</a>
</body>
</html>