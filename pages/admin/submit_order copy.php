<?php
require_once '../../src/db/db_connection.php'; // Include your database connection file

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $productIds = $_POST['product_ids'];
    $quantities = $_POST['quantities'];
    $productPrices = $_POST['product_prices'];
    $totalAmount = 0;

    foreach ($quantities as $index => $quantity) {
        $totalAmount += $quantity * $productPrices[$index];
    }

    // Insert order into orders table
    $stmt = $conn->prepare("INSERT INTO orders (total_amount) VALUES (?)");
    $stmt->bind_param("d", $totalAmount);
    $stmt->execute();
    $orderId = $stmt->insert_id;
    $stmt->close();

    // Insert order details into order_details table
    $stmt = $conn->prepare("INSERT INTO order_details (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
    foreach ($productIds as $index => $productId) {
        $quantity = $quantities[$index];
        $price = $productPrices[$index];
        $stmt->bind_param("iiid", $orderId, $productId, $quantity, $price);
        $stmt->execute();
    }
    $stmt->close();

    $conn->close();

    header("Location: orders.php");
    exit();
}
?>

<?php
// Start the session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login.php if no user is logged in
    header('Location: ../../login.php');
    exit();
}

// Database connection
$conn = new mysqli('localhost', 'root', '', 'your_database_name');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the logged-in user's ID
$user_id = $_SESSION['user_id'];

// Insert the order into the orders table
$sql = "INSERT INTO orders (user_id) VALUES ('$user_id')";
if ($conn->query($sql) === TRUE) {
    $order_id = $conn->insert_id;

    // Insert each product into the order_items table
    foreach ($_POST['product_ids'] as $index => $product_id) {
        $quantity = $_POST['quantities'][$index];
        $sql = "INSERT INTO order_items (order_id, product_id, quantity) VALUES ('$order_id', '$product_id', '$quantity')";
        $conn->query($sql);
    }

    // Redirect to a success page
    header('Location: order_success.php');
    exit();
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();
?>