<?php
include '../../src/db/db_connection.php';

// Assume $_POST['product_ids'], $_POST['quantities'], and $_POST['product_prices'] are submitted
$orderNumber = $_POST['order_number'];
$productIds = $_POST['product_ids'];
$quantities = $_POST['quantities'];
$productPrices = $_POST['product_prices'];
$deletedProducts = explode(',', $_POST['deleted_products']);

// Validate product IDs
try {
    if (!empty($productIds)) {
        $placeholders = rtrim(str_repeat('?,', count($productIds)), ',');
        $stmt = $pdo->prepare("SELECT id FROM products WHERE id IN ($placeholders)");
        $stmt->execute($productIds);
        $validProductIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // Filter valid product IDs
        foreach ($productIds as $index => $productId) {
            if (!in_array($productId, $validProductIds)) {
                unset($productIds[$index], $quantities[$index], $productPrices[$index]);
            }
        }

        // Reindex arrays to maintain proper alignment
        $productIds = array_values($productIds);
        $quantities = array_values($quantities);
        $productPrices = array_values($productPrices);
    }
} catch (Exception $e) {
    die("Error validating product IDs: " . $e->getMessage());
}

// Start a transaction
$pdo->beginTransaction();

try {
    // Delete marked products from the order
    if (!empty($deletedProducts)) {
        $placeholders = rtrim(str_repeat('?,', count($deletedProducts)), ',');
        $stmt = $pdo->prepare("DELETE FROM orders WHERE order_number = ? AND product_id IN ($placeholders)");
        $stmt->execute(array_merge([$orderNumber], $deletedProducts));
    }

    // Insert or update products in the order
    for ($i = 0; $i < count($productIds); $i++) {
        $productId = $productIds[$i];
        $quantity = $quantities[$i];
        $price = $productPrices[$i];

        // Check if the order already contains the product
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE order_number = ? AND product_id = ?");
        $stmt->execute([$orderNumber, $productId]);
        $exists = $stmt->fetchColumn();

        if ($exists) {
            // Update existing record
            $stmt = $pdo->prepare("UPDATE orders SET quantity = ?, price = ? WHERE order_number = ? AND product_id = ?");
            $stmt->execute([$quantity, $price, $orderNumber, $productId]);
        } else {
            // Insert new record
            $stmt = $pdo->prepare("INSERT INTO orders (order_number, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            $stmt->execute([$orderNumber, $productId, $quantity, $price]);
        }
    }

    // Commit transaction
    $pdo->commit();
    header('Location: order_records.php?status=success');
} catch (Exception $e) {
    // Rollback on error
    $pdo->rollBack();
    error_log("Error during order update: " . $e->getMessage());
    die("Error processing the order. Please check the logs for details.");
}

?>
