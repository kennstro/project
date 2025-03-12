<?php
// Prevent direct access
if (!defined('ALLOW_INCLUDE')) {
    header("Location: login.php");
    exit;
}

require 'db.php'; // Include database connection

function user_exists($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetchColumn() > 0;
}

function ban_user($user_id) {
    if (!user_exists($user_id)) {
        return false; // User ID does not exist
    }

    global $pdo;
    $stmt = $pdo->prepare("UPDATE users SET banned = TRUE WHERE id = ?");
    return $stmt->execute([$user_id]);
}

function unban_user($user_id) {
    if (!user_exists($user_id)) {
        return false; // User ID does not exist
    }

    global $pdo;
    $stmt = $pdo->prepare("UPDATE users SET banned = FALSE WHERE id = ?");
    return $stmt->execute([$user_id]);
}
?>