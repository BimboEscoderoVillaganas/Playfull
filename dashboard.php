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

// Get the current page name
$current_page = basename($_SERVER['PHP_SELF']);
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
            <a href="dashboard.php" class="text-white nav-link <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">
                <i class="bi bi-house-door me-2"></i>Dashboard
            </a>
        </li>
        <li>
            <a href="products.php" class="text-white nav-link <?php echo $current_page == 'products.php' ? 'active' : ''; ?>">
                <i class="bi bi-box-seam me-2"></i>Products
            </a>
        </li>
        <li>
            <a href="add_order.php" class="text-white nav-link <?php echo $current_page == 'add_order.php' ? 'active' : ''; ?>">
                <i class="bi bi-plus-circle me-2"></i>Add Order
            </a>
        </li>
        <li>
            <a href="list_orders.php" class="text-white nav-link <?php echo $current_page == 'list_orders.php' ? 'active' : ''; ?>">
                <i class="bi bi-list-ul me-2"></i>Order Records
            </a>
        </li>
        <li>
            <a href="paid_list.php" class="text-white nav-link <?php echo $current_page == 'paid_list.php' ? 'active' : ''; ?>">
                <i class="bi bi-cash-stack me-2"></i>Paid List
            </a>
        </li>
        <li>
            <a href="#settingsSubmenu" data-bs-toggle="collapse" class="text-white nav-link <?php echo in_array($current_page, ['list_employee.php', 'list_user.php']) ? 'active' : ''; ?>" onclick="toggleCaret(this)">
                <i class="bi bi-gear me-2"></i>Accounts <i class="bi bi-caret-down ms-2"></i>
            </a>
            <ul id="settingsSubmenu" class="collapse list-unstyled <?php echo in_array($current_page, ['list_employee.php', 'list_user.php']) ? 'show' : ''; ?>">
                <li><a href="list_employee.php" class="text-white nav-link <?php echo $current_page == 'list_employee.php' ? 'active' : ''; ?>" style="font-size: 12px;"><i class="bi bi-people me-2"></i>Employee List</a></li>
                <li><a href="list_user.php" class="text-white nav-link <?php echo $current_page == 'list_user.php' ? 'active' : ''; ?>" style="font-size: 12px;"><i class="bi bi-person me-2"></i>User List</a></li>
            </ul>
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

    function toggleCaret(element) {
        const caret = element.querySelector('.bi-caret-down, .bi-caret-up');
        caret.classList.toggle('bi-caret-down');
        caret.classList.toggle('bi-caret-up');
    }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/main.js"></script>
</body>
</html>