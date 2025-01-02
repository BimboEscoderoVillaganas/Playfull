<?php

// Database connection
require_once '../../src/db/db_connection.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login.php if no user is logged in
    header('Location: ../../login.php');
    exit();
}

// Get the order number and created_at from the query parameters
$order_number = $_GET['order_number'] ?? null;
$created_at = $_GET['created_at'] ?? null;

if ($order_number && $created_at) {
    try {
        // Start a transaction
        $pdo->beginTransaction();

        // Get the order ID based on the order_number and created_at
        $stmt = $pdo->prepare("SELECT id FROM orders WHERE order_number = ? AND created_at = ?");
        $stmt->execute([$order_number, $created_at]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($order) {
            $order_id = $order['id'];

            // Delete from order_details table using order_id
            $stmt = $pdo->prepare("DELETE FROM order_details WHERE order_id = ?");
            $stmt->execute([$order_id]);

            // Delete from orders table
            $stmt = $pdo->prepare("DELETE FROM orders WHERE id = ?");
            $stmt->execute([$order_id]);
        }

        // Commit the transaction
        $pdo->commit();
    } catch (Exception $e) {
        // Rollback the transaction in case of an error
        $pdo->rollBack();
        error_log($e->getMessage());
        header('Location: order_records.php?error=Unable to delete the order');
        exit();
    }

    // Redirect back to the order records page
    header('Location: order_records.php?success=Order deleted successfully');
    exit();
} else {
    header('Location: order_records.php?error=Invalid request');
    exit();
}
?>
