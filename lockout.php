<?php
require 'db.php'; // Include database connection

// Define constants for lockout criteria
define('MAX_FAILED_ATTEMPTS', 5); // Maximum allowed failed attempts
define('LOCKOUT_DURATION', 900); // Lockout duration in seconds (15 minutes)

// Log a failed login attempt
function log_failed_attempt($user_id, $ip_address) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO failed_logins (user_id, ip_address, attempt_time) VALUES (?, ?, NOW())");
    $stmt->execute([$user_id, $ip_address]);
}

// Check if the account is locked
function is_account_locked($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM failed_logins WHERE user_id = ? AND attempt_time > NOW() - INTERVAL ? SECOND");
    $stmt->execute([$user_id, LOCKOUT_DURATION]);
    $failed_attempts = $stmt->fetchColumn();
    return $failed_attempts >= MAX_FAILED_ATTEMPTS;
}

// Clear failed login attempts (on successful login)
function clear_failed_attempts($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM failed_logins WHERE user_id = ?");
    $stmt->execute([$user_id]);
}
?>