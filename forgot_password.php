<?php
require 'db.php'; // Include database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $ip_address = $_SERVER['REMOTE_ADDR'];

    // Basic validation
    if (empty($email)) {
        $error = "Email is required.";
    } else {
        // Sanitize input
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);

        // Check if user exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            // Generate password reset token
            $token = bin2hex(random_bytes(50));
            $user_id = $user['id'];
            $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

            // Store the token in the database with IP address and expiry date
            $stmt = $pdo->prepare("INSERT INTO password_resets (user_id, token, expires_at, ip_address) VALUES (?, ?, ?, ?)");
            $stmt->execute([$user_id, $token, $expiry, $ip_address]);

            // Send the reset link via email
            $reset_link = "http://yourwebsite.com/reset_password.php?token=$token";
            $subject = "Password Reset Request";
            $message = "Click the following link to reset your password: $reset_link";
            $headers = "From: no-reply@yourwebsite.com";

            if (mail($email, $subject, $message, $headers)) {
                $success = "A password reset link has been sent to your email.";
            } else {
                $error = "Failed to send email. Please try again.";
            }
        } else {
            $error = "No user found with that email address.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password</title>
</head>
<body>
    <h2>Forgot Password</h2>
    <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
    <?php if (isset($success)) echo "<p style='color:green;'>$success</p>"; ?>
    <form method="post" action="">
        <input type="email" name="email" placeholder="Email" required><br>
        <button type="submit">Send Reset Link</button>
    </form>
</body>
</html>