<?php
// Start the session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login.php if no user is logged in
    header('Location: ../../login.php');
    exit();
}

// Get the logged-in user's name
$username = htmlspecialchars($_SESSION['username']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../../assets/img/logo.webp" type="image/x-icon">
    <title>Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.5/font/bootstrap-icons.min.css" rel="stylesheet">

    <link rel="stylesheet" href="css/main.css">
</head>
<body>
    <!-- Header Section -->
    <header class="navbar navbar-expand-lg navbar-light bg-light shadow-sm">
        <div class="container-fluid">
            <button class="btn btn-primary me-3 d-lg-none" id="sidebarToggle">
                <i class="bi bi-list"></i>
            </button>

            <a class="navbar-brand" href="#"><img src="../../assets/img/logo.webp" alt="PlayFull Bistro" style="width: 80px;border-radius: 20px;"></a>
            <div class="ms-auto d-flex align-items-center">
                <!-- Notifications -->
                <div class="dropdown me-3">
                    <button class="btn btn-light dropdown-toggle" type="button" id="notificationDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-bell"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notificationDropdown">
                        <li><a class="dropdown-item" href="#">No new notifications</a></li>
                    </ul>
                </div>

                <!-- User Profile Dropdown -->
                <div class="dropdown">
                    <button class="btn btn-light dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-circle"></i> <?php echo $username; ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li><a class="dropdown-item" href="#"><i class="bi bi-person me-2"></i> Profile</a></li>
                        <li><a class="dropdown-item" href="#"><i class="bi bi-gear me-2"></i> Settings</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a href="#" class="dropdown-item" onclick="confirmLogout()">
                                <i class="bi bi-box-arrow-right me-2"></i> Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </header>
    
        <!-- Sidebar -->
    <div class="d-flex">
        <nav id="sidebar" class="bg-dark text-white">
            <ul class="list-unstyled">
                <li>
                    <a href="#" class="text-white nav-link">
                        <i class="bi bi-house-door me-2"></i>Home
                    </a>
                </li>
                <li>
                    <a href="#profileSubmenu" data-bs-toggle="collapse" class="text-white nav-link">
                        <i class="bi bi-person me-2"></i> Profile
                    </a>
                    <ul id="profileSubmenu" class="collapse list-unstyled">
                        <li><a href="#" class="text-white nav-link"><i class="bi bi-eye me-2"></i> View Profile</a></li>
                        <li><a href="#" class="text-white nav-link"><i class="bi bi-pencil-square me-2"></i>Edit Profile</a></li>
                    </ul>
                </li>
                <li>
                    <a href="#settingsSubmenu" data-bs-toggle="collapse" class="text-white nav-link">
                        <i class="bi bi-gear me-2"></i> Settings
                    </a>
                    <ul id="settingsSubmenu" class="collapse list-unstyled">
                        <li><a href="#" class="text-white nav-link"><i class="bi bi-shield-lock me-2"></i> Account Settings</a></li>
                        <li><a href="#" class="text-white nav-link"><i class="bi bi-lock me-2"></i> Privacy Settings</a></li>
                    </ul>
                </li>
                <li>
                    <a href="#" class="dropdown-item" onclick="confirmLogout()">
                        <i class="bi bi-box-arrow-right me-2"></i> Logout
                    </a>
                </li>
            </ul>
        </nav>

        <!-- Main Content -->
        <main class="flex-grow-1 p-4">
            <h1>Welcome to the Dashboard</h1>
            <p>This is your main content area.</p>
        </main>
    </div>


    
    <script>
    function confirmLogout() {
        if (confirm("Are you sure you want to logout?")) {
            window.location.href = "../../logout.php"; // Replace with your logout logic.
        }
    }
</script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/main.js"></script>
</body>
</html>
