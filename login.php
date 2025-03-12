<?php
session_start();
require 'db.php'; // Include database connection
require 'lockout.php'; // Include lockout functionality

// Check if the user is already logged in
if (isset($_SESSION['user_id'])) {
    // Redirect to home page if the user is already logged in
    header("Location: home.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $ip_address = $_SERVER['REMOTE_ADDR'];

    // Basic validation
    if (empty($username) || empty($password)) {
        $error = "All fields are required.";
    } else {
        // Sanitize input
        $username = htmlspecialchars($username);

        // Check if user exists
        $stmt = $pdo->prepare("SELECT id, username, password, role, banned, verified FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user) {
            if ($user['banned']) {
                $error = "Your account has been banned.";
            } elseif (!$user['verified']) {
                $error = "Please verify your email to activate your account.";
            } elseif (password_verify($password, $user['password'])) {
                // Clear failed login attempts on successful login
                clear_failed_attempts($user['id']);

                // Successful login
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username']; // Set the username in the session
                $_SESSION['role'] = $user['role'];

                session_regenerate_id(true); // Regenerate session ID to prevent session fixation

                header("Location: home.php");
                exit;
            } else {
                // Check if the account is locked
                if (is_account_locked($user['id'])) {
                    $error = "Account locked due to multiple failed login attempts. Try again later.";
                } else {
                    // Log failed login attempt with IP address
                    log_failed_attempt($user['id'], $ip_address);
                    $error = "Invalid username or password.";
                }
            }
        } else {
            $error = "Invalid username or password.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
</head>
<body>
    <h2>Login</h2>
    <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
    <form method="post" action="">
        <input type="text" name="username" placeholder="Username" required><br>
        <input type="password" name="password" placeholder="Password" required><br>
        <button type="submit">Login</button>
    </form>
</body>
</html>