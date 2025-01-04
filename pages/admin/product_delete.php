<?php
// Include the database connection file
require_once '../../src/db/db_connection.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit();
}

// Get product ID and image from the URL
$product_id = $_GET['id'];
$product_image = $_GET['image'];

try {
    // Begin transaction
    $pdo->beginTransaction();

    // Fetch the product details
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();

    if ($product) {
        // Move the product to the archive table
        $archiveStmt = $pdo->prepare(
            "INSERT INTO products_archive (id, image, product_name, description, quantity, price) 
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        $archiveStmt->execute([
            $product['id'],
            $product['image'],
            $product['product_name'],
            $product['description'],
            $product['quantity'],
            $product['price']
        ]);

        // Update related tables to maintain referential integrity
        $updateOrderDetailsStmt = $pdo->prepare(
            "UPDATE order_details 
             SET product_name = ?, product_id = NULL 
             WHERE product_id = ?"
        );
        $updateOrderDetailsStmt->execute([$product['product_name'], $product_id]);
        

        // Delete the product from the original table
        $deleteStmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $deleteStmt->execute([$product_id]);
    }

    // Commit transaction
    $pdo->commit();

} catch (Exception $e) {
    $pdo->rollBack();
    die("Error: " . $e->getMessage());
}

// Redirect back to the products page
header('Location: products.php');
exit();
?>
