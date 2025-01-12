<?php

// Database connection
require_once '../../src/db/db_connection.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login.php if no user is logged in
    header('Location: ../../login.php');
    exit();
}

// Get the order number from the query parameters
$order_number = $_GET['order_number'] ?? null;

if ($order_number) {
    try {
        // Start a transaction
        $pdo->beginTransaction();

        // Fetch all orders with the given order_number
        $stmt = $pdo->prepare("SELECT id, product_id, quantity FROM orders WHERE order_number = ?");
        $stmt->execute([$order_number]);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // If no orders found with the provided order_number
        if (!$orders) {
            header('Location: order_records.php?error=No orders found with this number');
            exit();
        }

        // Check for served or paid status for the orders
        $deleteOrders = [];

        foreach ($orders as $order) {
            $order_id = $order['id'];
            $product_id = $order['product_id'];
            $quantity = $order['quantity'];

            // Check if the order has been served or paid
            $stmt = $pdo->prepare("SELECT 1 FROM served WHERE order_id = ? LIMIT 1");
            $stmt->execute([$order_id]);
            $served = $stmt->fetch(PDO::FETCH_ASSOC);

            $stmt = $pdo->prepare("SELECT 1 FROM paid WHERE order_id = ? LIMIT 1");
            $stmt->execute([$order_id]);
            $paid = $stmt->fetch(PDO::FETCH_ASSOC);

            // If the order is neither served nor paid, mark it for deletion
            if (!$served && !$paid) {
                $deleteOrders[] = $order;
            }
        }

        // If there are any orders to delete, proceed with deletion
        if ($deleteOrders) {
            $updateStmt = $pdo->prepare("UPDATE products SET quantity = quantity + ? WHERE id = ?");

            // Restore quantities in the products table and delete orders
            foreach ($deleteOrders as $order) {
                // Restore product quantity
                $updateStmt->execute([$order['quantity'], $order['product_id']]);

                // Delete the order from the orders table
                $stmt = $pdo->prepare("DELETE FROM orders WHERE id = ?");
                $stmt->execute([$order['id']]);
            }

            // Commit the transaction
            $pdo->commit();

            // Redirect with success message
            header('Location: order_records.php?success=Orders deleted successfully');
            exit();
        } else {
            // If no deletable orders found
            header('Location: order_records.php?error=No deletable orders found (some are served/paid)');
            exit();
        }

    } catch (Exception $e) {
        // Rollback the transaction in case of an error
        $pdo->rollBack();
        error_log($e->getMessage());
        header('Location: order_records.php?error=Unable to delete orders');
        exit();
    }
} else {
    header('Location: order_records.php?error=Invalid request');
    exit();
}
?>
