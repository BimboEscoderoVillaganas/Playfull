<?php
require_once '../../src/db/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = intval($_POST['product_id']);
    $addQuantity = intval($_POST['add_quantity']);

    if ($productId > 0 && $addQuantity >= 0) {
        $stmt = $pdo->prepare("UPDATE products SET quantity = quantity + ? WHERE id = ?");
        if ($stmt->execute([$addQuantity, $productId])) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database update failed.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid input data.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
