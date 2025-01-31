<?php
require '../../src/db/db_connection.php'; // Include the database connection

if (isset($_GET['order_number']) && isset($_GET['created_at'])) {
    $order_number = $_GET['order_number'];
    $created_at = $_GET['created_at'];

    // Retrieve all order IDs with the same order_number that exist in orders or served, but not in paid
    $query = "SELECT o.id 
              FROM orders o 
              LEFT JOIN paid p ON o.id = p.order_id 
              WHERE o.order_number = :order_number AND p.order_id IS NULL
              UNION
              SELECT s.order_id 
              FROM served s 
              LEFT JOIN paid p ON s.order_id = p.order_id 
              WHERE s.order_id IN (SELECT id FROM orders WHERE order_number = :order_number) AND p.order_id IS NULL";
    
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':order_number', $order_number, PDO::PARAM_STR);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        while ($order = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $order_id = $order['id'];

            // Insert into paid table for each order
            $insert_query = "INSERT INTO paid (order_id) VALUES (:order_id)";
            $insert_stmt = $pdo->prepare($insert_query);
            $insert_stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);

            if (!$insert_stmt->execute()) {
                echo "<script>
                        alert('Error marking order as paid.');
                        window.location.href = 'served_order.php';
                      </script>";
                exit;
            }

            // Remove from served table if exists
            $delete_served_query = "DELETE FROM served WHERE order_id = :order_id";
            $delete_served_stmt = $pdo->prepare($delete_served_query);
            $delete_served_stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
            $delete_served_stmt->execute();
        }

        echo "<script>
                alert('All unpaid orders marked as paid and removed from served successfully.');
                window.location.href = 'served_order.php';
              </script>";
    } else {
        echo "<script>
                alert('No unpaid orders found for this order number.');
                window.location.href = 'served_order.php';
              </script>";
    }
}
?>