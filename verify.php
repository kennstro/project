<?php
require 'db.php'; // Include database connection

if (isset($_GET['token']) && isset($_GET['email'])) {
    $token = $_GET['token'];
    $email = $_GET['email'];

    // Verify the token and email
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND verification_token = ?");
    $stmt->execute([$email, $token]);
    $user = $stmt->fetch();

    if ($user) {
        // Update user status to verified
        $stmt = $pdo->prepare("UPDATE users SET verified = 1, verification_token = NULL WHERE id = ?");
        if ($stmt->execute([$user['id']])) {
            $success = "Your email has been verified successfully.";
        } else {
            $error = "Failed to verify email.";
        }
    } else {
        $error = "Invalid verification link.";
    }
} else {
    $error = "Invalid verification link.";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Email Verification</title>
</head>
<body>
    <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
    <?php if (isset($success)) echo "<p style='color:green;'>$success</p>"; ?>
    <a href="login.php">Login</a>
</body>
</html>