<?php
require_once '../../src/db/db_connection.php'; // Include your database connection file

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
        foreach ($productIds as $index => $productId) {
            // Insert the order
            $stmt = $pdo->prepare("INSERT INTO orders (order_number, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            $stmt->execute([$orderNumber, $productId, $quantities[$index], $productPrices[$index]]);

            // Deduct the quantity from the products table
            $updateStmt = $pdo->prepare("UPDATE products SET quantity = quantity - ? WHERE id = ?");
            $updateStmt->execute([$quantities[$index], $productId]);
        }

        // Commit transaction
        $pdo->commit();

        // Redirect with success message
        header("Location: add_order.php?message=Order successfully added and quantities updated!&type=success");
        exit();
    } catch (PDOException $e) {
        // Rollback transaction
        $pdo->rollBack();

        // Redirect with error message
        header("Location: add_order.php?message=Error: " . urlencode($e->getMessage()) . "&type=danger");
        exit();
    }
}
?>
