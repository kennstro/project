<?php
require 'db.php'; // Include database connection
require 'bad_words.php'; // Include list of bad words

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $ip_address = $_SERVER['REMOTE_ADDR'];

    // Basic validation
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = "All fields are required.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Check for inappropriate words in username
        foreach ($bad_words as $bad_word) {
            if (strpos(strtolower($username), strtolower($bad_word)) !== false) {
                $error = "Username contains inappropriate words.";
                break;
            }
        }

        // Validate username to allow letters, numbers, underscores, and spaces (but not consecutive spaces)
        if (!isset($error) && !preg_match('/^[a-zA-Z0-9_]+( [a-zA-Z0-9_]+)*$/', $username)) {
            $error = "Username can only contain letters, numbers, underscores, and spaces (but not consecutive spaces).";
        }

        if (!isset($error)) {
            // Sanitize and hash password
            $username = htmlspecialchars($username);
            $email = filter_var($email, FILTER_SANITIZE_EMAIL);
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = "Invalid email format.";
            } else {
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);

                // Check if username or email already exists
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? OR email = ?");
                $stmt->execute([$username, $email]);
                $count = $stmt->fetchColumn();

                if ($count > 0) {
                    $error = "Username or email is already taken.";
                } else {
                    // Generate a unique verification token
                    $token = bin2hex(random_bytes(16));
                    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, ip_address, verification_token, verified, role, banned) VALUES (?, ?, ?, ?, ?, 0, 'user', 0)");
                    if ($stmt->execute([$username, $email, $hashed_password, $ip_address, $token])) {
                        // Send verification email
                        $verification_link = "http://yourdomain.com/verify.php?token=$token&email=$email";
                        $subject = "Email Verification";
                        $message = "Please click the following link to verify your email: $verification_link";
                        $headers = "From: no-reply@yourdomain.com\r\n";
                        if (mail($email, $subject, $message, $headers)) {
                            $success = "Registration successful. Please check your email to verify your account.";
                        } else {
                            $error = "Failed to send verification email.";
                        }
                    } else {
                        $error = "An error occurred. Please try again.";
                    }
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
</head>
<body>
    <h2>Register</h2>
    <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
    <?php if (isset($success)) echo "<p style='color:green;'>$success</p>"; ?>
    <form method="post" action="">
        <input type="text" name="username" placeholder="Username" required><br>
        <input type="email" name="email" placeholder="Email" required><br>
        <input type="password" name="password" placeholder="Password" required><br>
        <input type="password" name="confirm_password" placeholder="Confirm Password" required><br>
        <button type="submit">Register</button>
    </form>
</body>
</html>