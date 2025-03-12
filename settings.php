<?php
session_start();
require 'db.php'; // Include database connection
require 'bad_words.php'; // Include list of bad words

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_username = trim($_POST['username']);
    $new_email = trim($_POST['email']);
    $new_password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Initialize error message
    $error = null;

    // Validate username
    if (!empty($new_username)) {
        if (!preg_match('/^[a-zA-Z0-9_]+( [a-zA-Z0-9_]+)*$/', $new_username)) {
            $error = "Username can only contain letters, numbers, underscores, and spaces (but not consecutive spaces).";
        } else {
            foreach ($bad_words as $bad_word) {
                if (strpos(strtolower($new_username), strtolower($bad_word)) !== false) {
                    $error = "Username contains inappropriate words.";
                    break;
                }
            }

            if (!isset($error)) {
                // Check if username already exists
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? AND id != ?");
                $stmt->execute([$new_username, $user_id]);
                if ($stmt->fetchColumn() > 0) {
                    $error = "Username already taken.";
                }
            }
        }
    }

    // Validate email
    if (!empty($new_email)) {
        $new_email = filter_var($new_email, FILTER_SANITIZE_EMAIL);
        if (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
            $error = "Invalid email address.";
        } else {
            // Check if email already exists
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$new_email, $user_id]);
            if ($stmt->fetchColumn() > 0) {
                $error = "Email already taken.";
            }
        }
    }

    // Validate password
    if (!empty($new_password) || !empty($confirm_password)) {
        if ($new_password !== $confirm_password) {
            $error = "Passwords do not match.";
        }
    }

    // Update user information if no errors
    if (!isset($error)) {
        if (!empty($new_username)) {
            $stmt = $pdo->prepare("UPDATE users SET username = ? WHERE id = ?");
            $stmt->execute([$new_username, $user_id]);
        }

        if (!empty($new_email)) {
            $stmt = $pdo->prepare("UPDATE users SET email = ? WHERE id = ?");
            $stmt->execute([$new_email, $user_id]);
        }

        if (!empty($new_password)) {
            $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashed_password, $user_id]);
        }

        $success = "Settings updated successfully.";
    }
}

// Fetch current user information
$stmt = $pdo->prepare("SELECT username, email FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

?>
<!DOCTYPE html>
<html>
<head>
    <title>User Settings</title>
</head>
<body>
    <h2>User Settings</h2>
    <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
    <?php if (isset($success)) echo "<p style='color:green;'>$success</p>"; ?>
    <form method="post" action="">
        <label>Username:</label><br>
        <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>"><br><br>
        <label>Email:</label><br>
        <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>"><br><br>
        <label>New Password:</label><br>
        <input type="password" name="password"><br><br>
        <label>Confirm New Password:</label><br>
        <input type="password" name="confirm_password"><br><br>
        <button type="submit">Update Settings</button>
    </form>
</body>
</html>