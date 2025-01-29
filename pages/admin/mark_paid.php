<?php
require '../../src/db/db_connection.php'; // Include the database connection

if (isset($_GET['order_number']) && isset($_GET['created_at'])) {
    $order_number = $_GET['order_number'];
    $created_at = $_GET['created_at'];

    // Retrieve all order IDs with the same order_number
    $query = "SELECT id FROM orders WHERE order_number = :order_number";
    
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':order_number', $order_number, PDO::PARAM_STR);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        // Loop through all orders with the same order_number
        while ($order = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $order_id = $order['id'];

            // Insert into paid table for each order
            $insert_query = "INSERT INTO paid (order_id) VALUES (:order_id)";
            $insert_stmt = $pdo->prepare($insert_query);
            $insert_stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);

            // If any insert fails, show an error message and break out of the loop
            if (!$insert_stmt->execute()) {
                echo "<script>
                        alert('Error marking order as paid.');
                        window.location.href = 'served_order.php';
                      </script>";
                exit;
            }

            // Check if the order is in the served table, and remove it if it exists
            $check_served_query = "SELECT id FROM served WHERE order_id = :order_id";
            $check_served_stmt = $pdo->prepare($check_served_query);
            $check_served_stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
            $check_served_stmt->execute();

            if ($check_served_stmt->rowCount() > 0) {
                // Remove the order from the served table
                $delete_served_query = "DELETE FROM served WHERE order_id = :order_id";
                $delete_served_stmt = $pdo->prepare($delete_served_query);
                $delete_served_stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
                $delete_served_stmt->execute();
            }
        }

        // Success message and redirection to served_order.php
        echo "<script>
                alert('All orders marked as paid and removed from served successfully.');
                window.location.href = 'served_order.php';
              </script>";
    } else {
        // Order not found message and redirection to served_order.php
        echo "<script>
                alert('Order not found.');
                window.location.href = 'served_order.php';
              </script>";
    }
}
?>
