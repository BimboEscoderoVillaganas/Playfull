<?php 
// Include the database connection
require_once 'src/db/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get user input
    $username = $_POST['username'];
    $password = $_POST['password'];
    $email = $_POST['email'];
    $phone_number = $_POST['phone_number'];

    // Check if email or phone number already exists in the database
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email OR phone_number = :phone_number");
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':phone_number', $phone_number);
    $stmt->execute();
    $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existingUser) {
        // Check for which field is already taken
        if ($existingUser['email'] == $email) {
            $errorMessage = 'The email address is already in use. Please choose another email.';
        } elseif ($existingUser['phone_number'] == $phone_number) {
            $errorMessage = 'The phone number is already in use. Please choose another phone number.';
        }
    } else {
        // Hash the password and insert the user into the database
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        try {
            // Insert the user into the database with default user_type as 'user'
            $stmt = $pdo->prepare("INSERT INTO users (username, password, email, phone_number, user_type) 
                                   VALUES (:username, :password, :email, :phone_number, 'user')");
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':phone_number', $phone_number);

            if ($stmt->execute()) {
                // Store user data in session
                $_SESSION['user_id'] = $pdo->lastInsertId(); // Get the last inserted user ID
                $_SESSION['username'] = $username;
                $_SESSION['email'] = $email;
                $_SESSION['phone_number'] = $phone_number;
                $_SESSION['user_type'] = 'user'; // Set default user type

                // Success message and redirect using JavaScript
                echo "<script>
                    alert('Welcome $username! Registration successful.');
                    window.location.href = 'pages/users/dashboard.php';
                </script>";
                exit;
            } else {
                $errorMessage = 'There was an error during registration. Please try again.';
            }
        } catch (PDOException $e) {
            $errorMessage = 'Database error: ' . $e->getMessage();
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/signup.css">
    <title>PlayFull Bistro - Sign Up</title>
    <style>
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgb(0, 0, 0);
            background-color: rgba(0, 0, 0, 0.4);
        }
        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 500px;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }
        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
</head>
<body>

    <div class="signup-container">
        <form action="signup.php" method="POST" class="signup-form">
            <h2>Sign Up</h2>
            <div class="input-group">
                <label for="username">Username:</label>
                <input type="text" name="username" required>
            </div>
            <div class="input-group">
                <label for="password">Password:</label>
                <input type="password" name="password" required>
            </div>
            <div class="input-group">
                <label for="email">Email:</label>
                <input type="email" name="email" required>
            </div>
            <div class="input-group">
                <label for="phone_number">Phone Number:</label>
                <input type="text" name="phone_number" required>
            </div>
            <button type="submit">Sign Up</button>
            <button type="button" class="cancel-btn" onclick="window.location.href='index.php';">Cancel</button>
            <div class="signup-link">
                Already have an account? <a href="login.php">Login here</a>
            </div>
        </form>
    </div>

    <!-- Modal -->
    <div id="errorModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <p id="errorMessage"></p>
        </div>
    </div>

    <script>
        // Open the modal with the error message
        <?php if (isset($errorMessage)): ?>
            document.getElementById('errorMessage').innerText = "<?php echo $errorMessage; ?>";
            var modal = document.getElementById('errorModal');
            modal.style.display = "block";
        <?php endif; ?>

        // Close the modal when the user clicks on <span> (x)
        var span = document.getElementsByClassName("close")[0];
        span.onclick = function() {
            var modal = document.getElementById('errorModal');
            modal.style.display = "none";
        }

        // Close the modal if the user clicks outside of the modal
        window.onclick = function(event) {
            var modal = document.getElementById('errorModal');
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    </script>
</body>
</html>
