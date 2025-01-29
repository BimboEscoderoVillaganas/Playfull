<?php 
require '../../src/db/db_connection.php'; // Include the database connection

if (isset($_GET['order_number'])) {
    $order_number = $_GET['order_number'];

    try {
        // Retrieve all order IDs using the order_number
        $query = "SELECT id FROM orders WHERE order_number = :order_number";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':order_number', $order_number, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Begin a transaction to ensure atomicity
            $pdo->beginTransaction();

            // Check each order if it already exists in the served table
            $check_query = "SELECT COUNT(*) FROM served WHERE order_id = :order_id";
            $check_stmt = $pdo->prepare($check_query);

            // Insert each order ID into the served table if not already marked as served
            $insert_query = "INSERT INTO served (order_id) VALUES (:order_id)";
            $insert_stmt = $pdo->prepare($insert_query);

            foreach ($orders as $order) {
                $order_id = $order['id'];

                // Check if the order is already served
                $check_stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
                $check_stmt->execute();

                // If the order is not already served, insert it into the served table
                if ($check_stmt->fetchColumn() == 0) {
                    $insert_stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
                    $insert_stmt->execute();
                }
            }

            // Commit the transaction
            $pdo->commit();

            // Success message
            echo "<script>
                if (confirm('All orders with order number \"$order_number\" have been marked as served. Do you want to view order records?')) {
                    window.location.href = 'order_records.php';
                }
            </script>";
        } else {
            // No orders found message
            echo "<script>
                alert('No orders found with order number \"$order_number\".');
                window.location.href = 'order_records.php';
            </script>";
        }
    } catch (Exception $e) {
        // Rollback the transaction in case of an error
        $pdo->rollBack();
        // Error message
        echo "<script>
            alert('An error occurred: " . addslashes($e->getMessage()) . "');
            window.location.href = 'order_records.php';
        </script>";
    }
}
?>
