<?php
require 'db.php';

function getUserById($userId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function addCurrency($userId, $amount, $description = '') {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE users SET currency_balance = currency_balance + ? WHERE id = ?");
    $stmt->execute([$amount, $userId]);

    // Log the transaction
    $stmt = $pdo->prepare("INSERT INTO currency_transactions (recipient_id, amount, transaction_type, description) VALUES (?, ?, 'credit', ?)");
    $stmt->execute([$userId, $amount, $description]);
}

function subtractCurrency($userId, $amount, $description = '') {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE users SET currency_balance = currency_balance - ? WHERE id = ?");
    $stmt->execute([$amount, $userId]);

    // Log the transaction
    $stmt = $pdo->prepare("INSERT INTO currency_transactions (sender_id, amount, transaction_type, description) VALUES (?, ?, 'debit', ?)");
    $stmt->execute([$userId, $amount, $description]);
}

function transferCurrency($senderId, $recipientId, $amount, $description) {
    global $pdo;

    // Start transaction
    $pdo->beginTransaction();

    try {
        // Get sender and recipient balances
        $stmt = $pdo->prepare("SELECT currency_balance FROM users WHERE id = ?");
        $stmt->execute([$senderId]);
        $senderBalance = $stmt->fetchColumn();

        if ($senderBalance < $amount) {
            throw new Exception('Insufficient balance.');
        }

        // Subtract from sender
        $stmt = $pdo->prepare("UPDATE users SET currency_balance = currency_balance - ? WHERE id = ?");
        $stmt->execute([$amount, $senderId]);

        // Add to recipient
        $stmt = $pdo->prepare("UPDATE users SET currency_balance = currency_balance + ? WHERE id = ?");
        $stmt->execute([$amount, $recipientId]);

        // Log the transaction for sender
        $stmt = $pdo->prepare("INSERT INTO currency_transactions (sender_id, recipient_id, amount, transaction_type, description) VALUES (?, ?, ?, 'transfer', ?)");
        $stmt->execute([$senderId, $recipientId, $amount, $description]);

        // Log the transaction for recipient
        $stmt = $pdo->prepare("INSERT INTO currency_transactions (sender_id, recipient_id, amount, transaction_type, description) VALUES (?, ?, ?, 'transfer', ?)");
        $stmt->execute([$senderId, $recipientId, $amount, $description]);

        // Commit transaction
        $pdo->commit();
        return "Transfer successful.";

    } catch (Exception $e) {
        // Rollback transaction
        $pdo->rollBack();
        return "Transfer failed: " . $e->getMessage();
    }
}
?>