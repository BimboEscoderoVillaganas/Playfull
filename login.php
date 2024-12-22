<?php
// Include the database connection
require_once 'src/db/db_connection.php';

// Initialize error messages and user data
$errorMessage = '';
$user = null;
$errorFields = '';
$showModal = false; // Flag to control modal visibility

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the user input
    $inputUsernameOrPhone = $_POST['username_or_phone'];
    $inputPassword = $_POST['password'];

    // Prepare the SQL query to check the username or phone number
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username_or_phone OR phone_number = :username_or_phone");
    $stmt->bindParam(':username_or_phone', $inputUsernameOrPhone);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verify user credentials
    if ($user) {
        // Check if password matches
        if (password_verify($inputPassword, $user['password'])) {
            // Check user type
            if ($user['user_type'] == 'unknown') {
                // Set the error message for unknown user type and flag for modal
                $errorMessage = 'Unknown user type. Please contact support.';
                $showModal = true; // Show the modal if user type is unknown
            } else {
                // Set session variables and redirect based on user type
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['phone_number'] = $user['phone_number'];
                $_SESSION['user_type'] = $user['user_type'];

                // Redirect based on user type
                switch ($user['user_type']) {
                    case 'admin':
                        header('Location: pages/admin/dashboard.php');
                        exit;
                    case 'employee':
                        header('Location: pages/employee/dashboard.php');
                        exit;
                    case 'user':
                        header('Location: pages/users/dashboard.php');
                        exit;
                    default:
                        // If user type is unknown, do not redirect, just show the modal
                        break;
                }
            }
        } else {
            // Invalid password
            $errorMessage = 'Password is incorrect.';
            $errorFields = 'password';
        }
    } else {
        // No user found
        $errorMessage = 'No user found with that username or phone number.';
        $errorFields = 'username_or_phone';
    }
}
?>

<!-- HTML Structure with Modal for Errors -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PlayFull Bistro - Login</title>
    <link rel="stylesheet" href="assets/css/login.css">
    <!-- Add Bootstrap for Modal -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="login-container">
        <div class="login-form">
            <h2>Login</h2>
            <?php if (!empty($errorMessage)) { ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $errorMessage; ?>
                </div>
            <?php } ?>
            <form action="login.php" method="post">
                <div class="input-group">
                    <label>Username or Phone Number:</label>
                    <input type="text" name="username_or_phone" required class="<?php echo (isset($errorFields) && $errorFields == 'username_or_phone') ? 'is-invalid' : ''; ?>">
                    <?php if (isset($errorFields) && $errorFields == 'username_or_phone') { ?>
                        <div class="invalid-feedback">This username or phone number does not match.</div>
                    <?php } ?>
                </div>
                <div class="input-group">
                    <label>Password:</label>
                    <input type="password" name="password" required class="<?php echo (isset($errorFields) && $errorFields == 'password') ? 'is-invalid' : ''; ?>">
                    <?php if (isset($errorFields) && $errorFields == 'password') { ?>
                        <div class="invalid-feedback">The password entered is incorrect.</div>
                    <?php } ?>
                </div>
                <button type="submit" class="login-btn">Login</button>
                <button type="button" class="cancel-btn" onclick="window.location.href='index.php'">Cancel</button>
            </form>
            <p>Don't have an account? <a href="signup.php">Sign Up</a></p>
        </div>
    </div>

    <!-- Modal for Unknown User Type Error -->
    <?php if ($showModal) { ?>
        <div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="errorModalLabel">Error</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>It seems like there is an issue with your user type.</p>
                        <ul>
                            <li><strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?></li>
                            <li><strong>Phone Number:</strong> <?php echo htmlspecialchars($user['phone_number']); ?></li>
                            <li><strong>Password:</strong> [hidden]</li>
                        </ul>
                        <p>Please contact support for further assistance.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        <script>
            // Show modal
            var myModal = new bootstrap.Modal(document.getElementById('errorModal'), {
                keyboard: false
            });
            myModal.show();
        </script>
    <?php } ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
