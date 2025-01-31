<?php
// Include database connection
include '../../src/db/db_connection.php';

// Get the product ID from the request
$data = json_decode(file_get_contents('php://input'), true);
$productId = $data['id'];

// Query to check if the product exists
$query = "SELECT COUNT(*) FROM products WHERE id = :id";
$stmt = $pdo->prepare($query);
$stmt->execute(['id' => $productId]);

$productExists = $stmt->fetchColumn() > 0;

echo json_encode(['exists' => $productExists]);
?>
