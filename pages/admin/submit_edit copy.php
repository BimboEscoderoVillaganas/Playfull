<?php
require_once '../../src/db/db_connection.php'; // Include your database connection file
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get input data
    $orderNumber = $_POST['order_number'];
    $createdAt = $_POST['created_at'];
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

        // Update the total amount in the 'orders' table
        $stmt = $pdo->prepare("UPDATE orders SET total_amount = ? WHERE order_number = ? AND created_at = ?");
        $stmt->execute([$totalAmount, $orderNumber, $createdAt]);

        // Update order details
        $stmt = $pdo->prepare("
    INSERT INTO order_details (order_number, product_id, product_name, quantity, price)
    VALUES (?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE 
        quantity = VALUES(quantity), 
        price = VALUES(price)
");


        foreach ($productIds as $index => $productId) {
            $quantity = $quantities[$index];
            $price = $productPrices[$index];
            $productName = $productNames[$index];

            // Execute insert or update for each product
            $stmt->execute([$orderNumber, $productId, $productName, $quantity, $price]);

            // Deduct quantity from the product inventory
            $updateStmt = $pdo->prepare("
                UPDATE products 
                SET quantity = quantity - ? 
                WHERE id = ?
            ");
            $updateStmt->execute([$quantity, $productId]);
        }

        // Commit transaction
        $pdo->commit();

        // Success message
        $_SESSION['message'] = "Order successfully updated!";
        $_SESSION['message_type'] = "success";

        // Redirect to the same page or another page
        header("Location: edit_order.php");
        exit();
    } catch (PDOException $e) {
        // Rollback transaction on error
        $pdo->rollBack();

        // Error message
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['message_type'] = "danger";

        // Redirect after error
        header("Location: edit_order.php");
        exit();
    }
}
?>
