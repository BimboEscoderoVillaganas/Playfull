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

        // Check if there are existing records for the same order_number
        $stmt = $pdo->prepare("SELECT id, created_at FROM orders WHERE order_number = ?");
        $stmt->execute([$orderNumber]);
        $existingOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($existingOrders) {
            // Fetch old order details
            $detailsStmt = $pdo->prepare("SELECT product_id, quantity FROM order_details WHERE order_id = ?");
            $oldOrderId = $existingOrders[0]['id']; // Assuming one active order per order_number
            $detailsStmt->execute([$oldOrderId]);
            $oldOrderDetails = $detailsStmt->fetchAll(PDO::FETCH_ASSOC);
        
            // Map old quantities by product ID
            $oldQuantities = [];
            foreach ($oldOrderDetails as $detail) {
                $oldQuantities[$detail['product_id']] = $detail['quantity'];
            }
        
            // Update product quantities based on the difference
            foreach ($productIds as $index => $productId) {
                $newQuantity = $quantities[$index];
                $oldQuantity = isset($oldQuantities[$productId]) ? $oldQuantities[$productId] : 0;
                $quantityDifference = $newQuantity - $oldQuantity;
        
                // Update product quantity in the `products` table
                $updateStmt = $pdo->prepare("UPDATE products SET quantity = quantity - ? WHERE id = ?");
                $updateStmt->execute([$quantityDifference, $productId]);
            }
        
            // Delete old order details
            $deleteDetailsStmt = $pdo->prepare("DELETE FROM order_details WHERE order_id = ?");
            $deleteDetailsStmt->execute([$oldOrderId]);
        
            // Insert new order details
            $stmt = $pdo->prepare("INSERT INTO order_details (order_id, order_number, product_id, product_name, quantity, price) VALUES (?, ?, ?, ?, ?, ?)");
            foreach ($productIds as $index => $productId) {
                $quantity = $quantities[$index];
                $price = $productPrices[$index];
                $productName = $productNames[$index];
                $stmt->execute([$oldOrderId, $orderNumber, $productId, $productName, $quantity, $price]);
            }
        
            // Update total amount in `orders` table
            $updateOrderStmt = $pdo->prepare("UPDATE orders SET total_amount = ? WHERE id = ?");
            $updateOrderStmt->execute([$totalAmount, $oldOrderId]);
        } else {
            // Handle new orders (no changes needed here)
            $stmt = $pdo->prepare("INSERT INTO orders (order_number, total_amount) VALUES (?, ?)");
            $stmt->execute([$orderNumber, $totalAmount]);
            $orderId = $pdo->lastInsertId();
        
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
        }
        

        // Commit transaction
        $pdo->commit();

        // Set success message
        $_SESSION['message'] = "Order successfully updated!";
        $_SESSION['message_type'] = "success";

        // Redirect after success
        header("Location: edit_order.php");
        exit();
    } catch (PDOException $e) {
        // Rollback transaction
        $pdo->rollBack();

        // Set error message
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['message_type'] = "danger";

        // Redirect after error
        header("Location: edit_order.php");
        exit();
    }
}
?>
