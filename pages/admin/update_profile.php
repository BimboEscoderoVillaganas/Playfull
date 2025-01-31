<?php
include '../../src/db/db_connection.php';

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $phone_number = $_POST['phone_number'];
    $password = $_POST['password']; // Get the password field

    try {
        // Hash password if it's provided
        if (!empty($password)) {
            $password = password_hash($password, PASSWORD_DEFAULT); // Hash password
            $query = "UPDATE users SET username = ?, email = ?, phone_number = ?, password = ? WHERE id = ?";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$username, $email, $phone_number, $password, $user_id]);
        } else {
            // If no password change, just update username, email, and phone number
            $query = "UPDATE users SET username = ?, email = ?, phone_number = ? WHERE id = ?";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$username, $email, $phone_number, $user_id]);
        }

        echo "Profile updated successfully.";
    } catch (PDOException $e) {
        echo "Error updating profile: " . $e->getMessage();
    }
} else {
    echo "Invalid request.";
}
?>
