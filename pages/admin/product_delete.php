<?php
// Include the database connection file
require_once '../../src/db/db_connection.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit();
}

// Check if the logged-in user is an admin
if ($_SESSION['user_type'] !== 'admin') {
    // Redirect unauthorized users to the homepage or an error page
    header('Location: 403.php'); // Use 403 Forbidden error page
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
        // Update the product's status to 'inactive' (mark as deleted)
        $updateStatusStmt = $pdo->prepare("UPDATE products SET status = 'inactive' WHERE id = ?");
        $updateStatusStmt->execute([$product_id]);
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
