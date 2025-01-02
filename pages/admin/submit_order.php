<?php
require_once '../../src/db/db_connection.php'; // Include your database connection file
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $orderNumber = $_POST['order_number']; // Get the order number from the form
    $productIds = $_POST['product_ids'];
    $quantities = $_POST['quantities'];
    $productPrices = $_POST['product_prices'];
    $productNames = $_POST['product_names'];
    $totalAmount = 0;

    foreach ($quantities as $index => $quantity) {
        $totalAmount += $quantity * $productPrices[$index];
    }

    try {
        // Begin transaction
        $pdo->beginTransaction();

        // Insert order into orders table
        $stmt = $pdo->prepare("INSERT INTO orders (order_number, total_amount) VALUES (?, ?)");
        $stmt->execute([$orderNumber, $totalAmount]);
        $orderId = $pdo->lastInsertId();

        // Insert order details into order_details table
        $stmt = $pdo->prepare("INSERT INTO order_details (order_id, order_number, product_id, product_name, quantity, price) VALUES (?, ?, ?, ?, ?, ?)");
        foreach ($productIds as $index => $productId) {
            $quantity = $quantities[$index];
            $price = $productPrices[$index];
            $productName = $productNames[$index];
            $stmt->execute([$orderId, $orderNumber, $productId, $productName, $quantity, $price]);

            // Deduct the quantity from the products table
            $updateStmt = $pdo->prepare("UPDATE products SET quantity = quantity - ? WHERE id = ?");
            $updateStmt->execute([$quantity, $productId]);
        }

        // Commit transaction
        $pdo->commit();

        // Set success message
        $_SESSION['message'] = "Order successfully added!";
        $_SESSION['message_type'] = "success";

        // Redirect after success
        header("Location: add_order.php");
        exit();
    } catch (PDOException $e) {
        // Rollback transaction
        $pdo->rollBack();

        // Set error message
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['message_type'] = "danger";

        // Redirect after error
        header("Location: add_order.php");
        exit();
    }
}
?>