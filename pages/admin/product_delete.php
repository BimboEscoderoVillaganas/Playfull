<?php
// Include the database connection file
require_once '../../src/db/db_connection.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login.php if no user is logged in
    header('Location: ../../login.php');
    exit();
}

// Get the product ID and image from the URL
$product_id = $_GET['id'];
$product_image = $_GET['image'];

// Delete the product from the database
$stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
$stmt->execute([$product_id]);

// Delete the image file if it exists
if (!empty($product_image) && file_exists($product_image)) {
    unlink($product_image);
}

// Redirect back to the products page
header('Location: products.php');
exit();
?>