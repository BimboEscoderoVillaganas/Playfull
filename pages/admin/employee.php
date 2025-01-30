<?php
require '../../src/db/db_connection.php'; // Include database connection

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit();
}

// Check if the logged-in user is an admin
if ($_SESSION['user_type'] !== 'admin') {
  // Redirect unauthorized users to the homepage or an error page
  header('Location: 403.php'); // Use 403 Forbidden error page
  exit();
}
// Get the logged-in user's name
$username = htmlspecialchars($_SESSION['username']);
// Get the logged-in user's email
$useremail = htmlspecialchars($_SESSION['email']);

// Handle adding a new employee (for AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_employee'])) {
  $response = ['success' => false, 'message' => 'Something went wrong!'];

  $username = trim($_POST['username']);
  $email = trim($_POST['email']);
  $phone_number = trim($_POST['phone_number']);
  $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash password

  if (!empty($username) && !empty($email) && !empty($phone_number) && !empty($_POST['password'])) {
      $insert_query = "INSERT INTO users (username, email, phone_number, password, user_type, status, created_at) 
                       VALUES (?, ?, ?, ?, 'employee', 'active', NOW())";
      $stmt = $pdo->prepare($insert_query);
      if ($stmt->execute([$username, $email, $phone_number, $password])) {
          $response = ['success' => true, 'message' => 'Employee added successfully!'];
      }
  } else {
      $response['message'] = 'All fields are required!';
  }

  echo json_encode($response);
  exit();
}
// Fetch employees and managers from the database
$query = "SELECT id, username, email, phone_number, created_at, status, user_type FROM users WHERE user_type IN ('employee', 'manager')";
$stmt = $pdo->prepare($query);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Separate employees and managers
$employees = array_filter($users, function($user) {
    return $user['user_type'] === 'employee';
});

$managers = array_filter($users, function($user) {
    return $user['user_type'] === 'manager';
});

// Handle Actions (Enable/Disable, Delete, Temporary Manager, Revert to Employee)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && isset($_POST['user_id'])) {
        $user_id = $_POST['user_id'];
        
        if ($_POST['action'] === 'toggle_status') {
            // Enable/Disable User
            $update_status_query = "UPDATE users SET status = IF(status = 'active', 'inactive', 'active') WHERE id = ?";
            $stmt = $pdo->prepare($update_status_query);
            $stmt->execute([$user_id]);
        
        } elseif ($_POST['action'] === 'delete') {
            // Delete User
            $delete_query = "DELETE FROM users WHERE id = ?";
            $stmt = $pdo->prepare($delete_query);
            $stmt->execute([$user_id]);
        
        } elseif ($_POST['action'] === 'set_manager') {
            // Set user_type = 'manager' for one day
            $today = date('Y-m-d');
            $tomorrow = date('Y-m-d', strtotime('+1 day'));
            
            $set_manager_query = "UPDATE users SET user_type = 'manager', enable_date = ?, disable_date = ? WHERE id = ?";
            $stmt = $pdo->prepare($set_manager_query);
            $stmt->execute([$today, $tomorrow, $user_id]);
        
        } elseif ($_POST['action'] === 'revert_to_employee') {
            // Revert user_type back to 'employee'
            $revert_to_employee_query = "UPDATE users SET user_type = 'employee' WHERE id = ?";
            $stmt = $pdo->prepare($revert_to_employee_query);
            $stmt->execute([$user_id]);
        }
        
        // Redirect to refresh the page
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

// Get today's date
$today = date('Y-m-d');

try {
    // Fetch product sales and calculate the average
    $query = "SELECT p.id, p.product_name, SUM(o.quantity) AS total_quantity, 
                     AVG(o.quantity) AS average_quantity
              FROM paid pa
              JOIN orders o ON pa.order_id = o.id
              JOIN products p ON o.product_id = p.id
              WHERE DATE(pa.paid_at) = ?
              GROUP BY p.id";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$today]);
    $salesData = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Filter products that reached or exceeded the average
    $productsExceedingAverage = [];
    foreach ($salesData as $data) {
        if ($data['total_quantity'] >= $data['average_quantity']) {
            $productsExceedingAverage[] = [
                'name' => $data['product_name'],
                'quantity' => $data['total_quantity']
            ];
        }
    }

    // Pass data to frontend
    $productCount = count($productsExceedingAverage);
} catch (PDOException $e) {
    die("Database query failed: " . $e->getMessage());
}
?>


<!DOCTYPE html>
<html lang="en">
  <head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>PlayFull Bistro Employee List</title>
    <meta
      content="width=device-width, initial-scale=1.0, shrink-to-fit=no"
      name="viewport"
    />
    <link
      rel="icon"
      href="../../assets/img/logo.webp"
      type="image/x-icon"
    />

    <!-- Fonts and icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.5/font/bootstrap-icons.min.css" rel="stylesheet">
    <script src="../../assets/js/plugin/webfont/webfont.min.js"></script>
    <script>
      WebFont.load({
        google: { families: ["Public Sans:300,400,500,600,700"] },
        custom: {
          families: [
            "Font Awesome 5 Solid",
            "Font Awesome 5 Regular",
            "Font Awesome 5 Brands",
            "simple-line-icons",
          ],
          urls: ["../../assets/css/fonts.min.css"],
        },
        active: function () {
          sessionStorage.fonts = true;
        },
      });
    </script>

    <!-- CSS Files -->
    <link rel="stylesheet" href="../../assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="../../assets/css/plugins.min.css" />
    <link rel="stylesheet" href="../../assets/css/kaiadmin.min.css" />

  </head>
  <body>
    <div class="wrapper">
      <!-- Sidebar -->
      <div class="sidebar" data-background-color="dark">
        <div class="sidebar-logo">
          <!-- Logo Header -->
          <div class="logo-header" data-background-color="dark">
            <a href="dashboard.php" class="logo">
              <img
                src="../../assets/img/logo.png"
                alt="navbar brand"
                class="navbar-brand"
                height="60"
                data-background-color="white"
                
              />
            </a>
            <div class="nav-toggle">
              <button class="btn btn-toggle toggle-sidebar">
                <i class="gg-menu-right"></i>
              </button>
              <button class="btn btn-toggle sidenav-toggler">
                <i class="gg-menu-left"></i>
              </button>
            </div>
            <button class="topbar-toggler more">
              <i class="gg-more-vertical-alt"></i>
            </button>
          </div>
          <!-- End Logo Header -->
        </div>
        <div class="sidebar-wrapper scrollbar scrollbar-inner">
          <div class="sidebar-content">
            <ul class="nav nav-secondary">
              <li class="nav-section">
                <span class="sidebar-mini-icon">
                  <i class="fa fa-ellipsis-h"></i>
                </span>
                <h4 class="text-section">Key Performans Indicator</h4>
              </li>
              <li class="nav-item">
              <a href="dashboard.php">
                  <i class="fas fa-home"></i>
                  <p>Dashboard</p>
                </a>
              </li>
              <li class="nav-item">
              <a href="sales.php">
                  <i class="far fa-chart-bar"></i>
                  <p>Sales Report</p>
                </a>
              </li>
              <li class="nav-section">
                <span class="sidebar-mini-icon">
                  <i class="fa fa-ellipsis-h"></i>
                </span>
                <h4 class="text-section">Order Management</h4>
              </li>
              <li class="nav-item">
              <a href="add_order.php">
                  <i class="bi bi-plus-circle me-2"></i>
                  <p>Add Orders</p>
                </a>
              </li>
              <li class="nav-item">
              <a href="order_records.php">
                  <i class="bi bi-list-ul me-2"></i>
                  <p>Order Records</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="served_order.php">
                    <i class="fas fa-clipboard-check"></i>
                    <p>Served Orders</p>
                </a>
            </li>
              <li class="nav-section">
                <span class="sidebar-mini-icon">
                  <i class="fa fa-ellipsis-h"></i>
                </span>
                <h4 class="text-section">Product Management</h4>
              </li>
              <li class="nav-item">
              <a href="products.php">
                  <i class="bi bi-box-seam me-2"></i>
                  <p>Products</p>
                </a>
              </li>
              <li class="nav-item">
                  <a href="products_add.php">
                      <i class="bi bi-plus-square me-2"></i>
                      <p>Add Products</p>
                  </a>
              </li>
              <li class="nav-item">
                  <a href="products_archive.php"> 
                      <i class="bi bi-archive me-2"></i>
                      <p>Products Archive</p> 
                  </a>
              </li>
              <li class="nav-section">
                <span class="sidebar-mini-icon">
                  <i class="fa fa-ellipsis-h"></i>
                </span>
                <h4 class="text-section"><i class="bi bi-gear me-2"></i>Accounts Settings</h4>
              </li>
              <li class="nav-item active">
                <a data-bs-toggle="collapse" href="#base">
                  <i class="fas fa-layer-group"></i>
                  <p>Account List</p>
                  <span class="caret"></span>
                </a>
                <div class="collapse" id="base">
                  <ul class="nav nav-collapse">
                    <li class="active">
                      <a href="employee.php">
                        <span class="sub-item"><i class="bi bi-people me-2"></i>Empployee List</span>
                      </a>
                    </li>
                    <li>
                      <a href="user.php">
                        <span class="sub-item"><i class="bi bi-person me-2"></i>User List</span>
                      </a>
                    </li>
                  </ul>
                </div>
              </li>
            </ul>
          </div>
        </div>
      </div>
      <!-- End Sidebar -->

      <div class="main-panel">
        <div class="main-header">
          <div class="main-header-logo">
            <!-- Logo Header -->
            <div class="logo-header" data-background-color="dark">
              <a href="dashboard.php" class="logo">
                <img
                  src="../../assets/img/logo.png"
                  alt="navbar brand"
                  class="navbar-brand"
                  height="20"
                />
              </a>
              <div class="nav-toggle">
                <button class="btn btn-toggle toggle-sidebar">
                  <i class="gg-menu-right"></i>
                </button>
                <button class="btn btn-toggle sidenav-toggler">
                  <i class="gg-menu-left"></i>
                </button>
              </div>
              <button class="topbar-toggler more">
                <i class="gg-more-vertical-alt"></i>
              </button>
            </div>
            <!-- End Logo Header -->
          </div>
          <!-- Navbar Header -->
          <nav
            class="navbar navbar-header navbar-header-transparent navbar-expand-lg border-bottom"
          >
            <div class="container-fluid">
              <nav
                class="navbar navbar-header-left navbar-expand-lg navbar-form nav-search p-0 d-none d-lg-flex"
              >
              </nav>

              <ul class="navbar-nav topbar-nav ms-md-auto align-items-center">
              <li class="nav-item topbar-icon dropdown hidden-caret">
                  <a
                    class="nav-link dropdown-toggle"
                    href="#"
                    id="notifDropdown"
                    role="button"
                    data-bs-toggle="dropdown"
                    aria-haspopup="true"
                    aria-expanded="false"
                  >
                    <i class="fa fa-bell"></i>
                    <span class="notification"><?php echo $productCount; ?></span>
                  </a>
                  <ul
                    class="dropdown-menu notif-box animated fadeIn"
                    aria-labelledby="notifDropdown"
                  >
                  <li>
      <div class="dropdown-title">
        You have <?php echo $productCount; ?> new notification<?php echo $productCount > 1 ? 's' : ''; ?>
      </div>
    </li>
    <li>
      <div class="notif-scroll scrollbar-outer">
        <div class="notif-center">
          <?php foreach ($productsExceedingAverage as $product): ?>
            <a href="#">
              <div class="notif-icon notif-primary">
                <i class="fa fa-box"></i>
              </div>
              <div class="notif-content">
                <span class="block"><?php echo htmlspecialchars($product['name']); ?></span>
                <span class="time"><?php echo htmlspecialchars($product['quantity']); ?> sold today</span>
              </div>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    </li>
    <li>
      <a class="see-all" href="javascript:void(0);">
        See all notifications
        <i class="fa fa-angle-right"></i>
      </a>
    </li>
  </ul>
</li>
                

                <li class="nav-item topbar-user dropdown hidden-caret">
                  <a
                    class="dropdown-toggle profile-pic"
                    data-bs-toggle="dropdown"
                    href="#"
                    aria-expanded="false"
                  >
                    <div class="avatar-sm">
                      <img
                        src="../../assets/img/profile.jpg"
                        alt="..."
                        class="avatar-img rounded-circle"
                      />
                    </div>
                    <span class="profile-username">
                      <span class="op-7">Hi,</span>
                      <span class="fw-bold"><?php echo $username; ?></span>
                    </span>
                  </a>
                  <ul class="dropdown-menu dropdown-user animated fadeIn">
                    <div class="dropdown-user-scroll scrollbar-outer">
                      <li>
                        <div class="user-box">
                          <div class="avatar-lg">
                            <img
                              src="../../assets/img/profile.jpg"
                              alt="image profile"
                              class="avatar-img rounded"
                            />
                          </div>
                          <div class="u-text">
                            <h4><?php echo $username; ?></h4>
                            <p class="text-muted"><?php echo $useremail; ?></p>
                            <a
                              href="profile.php"
                              class="btn btn-xs btn-secondary btn-sm"
                              >View Profile</a
                            >
                          </div>
                        </div>
                      </li>
                      <li>
                        <div class="dropdown-divider"></div>
                        <div style="display: flex; justify-content: flex-end;">
                          <button onclick="confirmLogout()" style="border-radius: 20px; background-color: red; color: white; border: none; padding: 5px 10px; cursor: pointer; margin-right: 10px;">
                              Logout
                          </button>
                      </div>
                      </li>
                    </div>
                  </ul>
                </li>
              </ul>
            </div>
          </nav>
          <!-- End Navbar -->
        </div>

        <div class="container"> 
    <div class="page-inner">
        <h2>Employee List</h2>
        <!-- Add Employee Button (Opens Modal) -->
        <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addEmployeeModal">
            Add Employee
        </button>
        <table class="table table-bordered mt-3">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Phone Number</th>
                    <th>Created At</th>
                    <th>User Type</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($employees)): ?>
                    <?php foreach ($employees as $employee): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($employee['id']); ?></td>
                            <td><?php echo htmlspecialchars($employee['username']); ?></td>
                            <td><?php echo htmlspecialchars($employee['email']); ?></td>
                            <td><?php echo htmlspecialchars($employee['phone_number']); ?></td>
                            <td><?php echo htmlspecialchars($employee['created_at']); ?></td>
                            <td><?php echo htmlspecialchars($employee['user_type']); ?></td>
                            <td>
                                <?php echo $employee['status'] === 'active' ? '<span class="text-success">Active</span>' : '<span class="text-danger">Inactive</span>'; ?>
                            </td>
                            <td>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="user_id" value="<?php echo $employee['id']; ?>">
                                    <button type="submit" name="action" value="toggle_status" class="btn btn-sm btn-warning">
                                        <?php echo $employee['status'] === 'active' ? 'Disable' : 'Enable'; ?>
                                    </button>
                                </form>

                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="user_id" value="<?php echo $employee['id']; ?>">
                                    <button type="submit" name="action" value="delete" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this employee?');">
                                        Delete
                                    </button>
                                </form>

                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="user_id" value="<?php echo $employee['id']; ?>">
                                    <button type="submit" name="action" value="set_manager" class="btn btn-sm btn-info">
                                        Set as Manager (1 Day)
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center">No employees found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <h2>Manager List</h2>
        <table class="table table-bordered mt-3">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Phone Number</th>
                    <th>Created At</th>
                    <th>User Type</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($managers)): ?>
                    <?php foreach ($managers as $manager): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($manager['id']); ?></td>
                            <td><?php echo htmlspecialchars($manager['username']); ?></td>
                            <td><?php echo htmlspecialchars($manager['email']); ?></td>
                            <td><?php echo htmlspecialchars($manager['phone_number']); ?></td>
                            <td><?php echo htmlspecialchars($manager['created_at']); ?></td>
                            <td><?php echo htmlspecialchars($manager['user_type']); ?></td>
                            <td>
                                <?php echo $manager['status'] === 'active' ? '<span class="text-success">Active</span>' : '<span class="text-danger">Inactive</span>'; ?>
                            </td>
                            <td>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="user_id" value="<?php echo $manager['id']; ?>">
                                    <button type="submit" name="action" value="revert_to_employee" class="btn btn-sm btn-secondary">
                                        Revert to Employee
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center">No managers found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>



<!-- Add Employee Modal -->
<div class="modal fade" id="addEmployeeModal" tabindex="-1" aria-labelledby="addEmployeeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addEmployeeModalLabel">Add New Employee</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="responseMessage" class="alert d-none"></div>
                <form id="addEmployeeForm">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="phone_number" class="form-label">Phone Number</label>
                        <input type="text" class="form-control" id="phone_number" name="phone_number" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Add Employee</button>
                </form>
            </div>
        </div>
    </div>
</div>


<!-- AJAX Script for Form Submission -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function () {
    $('#addEmployeeForm').submit(function (event) {
        event.preventDefault(); // Prevent page reload

        $.ajax({
            url: '', // Submits to the same PHP file
            type: 'POST',
            data: $(this).serialize() + '&add_employee=true',
            dataType: 'json',
            success: function (response) {
                let responseMessage = $('#responseMessage');
                if (response.success) {
                    responseMessage.removeClass('d-none alert-danger').addClass('alert-success').text(response.message);
                    setTimeout(function () {
                        location.reload(); // Refresh the page after success
                    }, 1500);
                } else {
                    responseMessage.removeClass('d-none alert-success').addClass('alert-danger').text(response.message);
                }
            }
        });
    });
});
</script>











        <footer class="footer">
          <div class="container-fluid d-flex justify-content-between">
            <nav class="pull-left">
              <ul class="nav">
                <li class="nav-item">
                  <a class="nav-link" target="_blank" href="https://bimboescoderovillaganas.github.io/Bimbo/">
                    BimDev
                  </a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" href="#"> Help </a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" href="#"> Licenses </a>
                </li>
              </ul>
            </nav>
            <div class="copyright">
              2024, made with <i class="fa fa-heart heart text-danger"></i> by
              <a href="https://github.com/BimboEscoderoVillaganas"  target="blank">BimDev</a>
            </div>
            <div>
              Distributed by
              <a target="_blank" href="https://bimboescoderovillaganas.github.io/Bimbo/">BimDev</a>.
            </div>
          </div>
        </footer>
      </div>

    </div>

        <script>
    function confirmLogout() {
        if (confirm("Are you sure you want to logout?")) {
            window.location.href = "../../logout.php"; // Replace with your logout logic.
        }
    }
    </script>
    <!--   Core JS Files   -->
    <script src="../../assets/js/core/jquery-3.7.1.min.js"></script>
    <script src="../../assets/js/core/popper.min.js"></script>
    <script src="../../assets/js/core/bootstrap.min.js"></script>

    <!-- jQuery Scrollbar -->
    <script src="../../assets/js/plugin/jquery-scrollbar/jquery.scrollbar.min.js"></script>

    <!-- Chart JS -->
    <script src="../../assets/js/plugin/chart.js/chart.min.js"></script>

    <!-- jQuery Sparkline -->
    <script src="../../assets/js/plugin/jquery.sparkline/jquery.sparkline.min.js"></script>

    <!-- Chart Circle -->
    <script src="../../assets/js/plugin/chart-circle/circles.min.js"></script>

    <!-- Datatables -->
    <script src="../../assets/js/plugin/datatables/datatables.min.js"></script>

    <!-- Bootstrap Notify -->
    <script src="../../assets/js/plugin/bootstrap-notify/bootstrap-notify.min.js"></script>

    <!-- jQuery Vector Maps -->
    <script src="../../assets/js/plugin/jsvectormap/jsvectormap.min.js"></script>
    <script src="../../assets/js/plugin/jsvectormap/world.js"></script>

    <!-- Sweet Alert -->
    <script src="../../assets/js/plugin/sweetalert/sweetalert.min.js"></script>

    <!-- Kaiadmin JS -->
    <script src="../../assets/js/kaiadmin.min.js"></script>

  </body>
</html>
