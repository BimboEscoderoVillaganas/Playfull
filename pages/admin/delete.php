<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include '../../src/db/db_connection.php'; // Adjust if needed

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Decode JSON request
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['id'])) {
        echo json_encode(['success' => false, 'message' => 'Missing product ID.']);
        exit;
    }

    $productId = intval($data['id']); // Sanitize input

    try {
        // Check if the product exists
        $checkQuery = "SELECT id FROM orders WHERE id = ?";
        $checkStmt = $pdo->prepare($checkQuery);
        $checkStmt->execute([$productId]);

        if ($checkStmt->rowCount() === 0) {
            echo json_encode(['success' => false, 'message' => 'Product not found in orders.']);
            exit;
        }

        // Delete the product from orders table
        $deleteQuery = "DELETE FROM orders WHERE id = ?";
        $deleteStmt = $pdo->prepare($deleteQuery);
        $deleteStmt->execute([$productId]);

        if ($deleteStmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Product deleted successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete product.']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>
