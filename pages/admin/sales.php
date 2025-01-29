<?php
// Database connection
require '../../src/db/db_connection.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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


$currentMonth = date('F'); // e.g., "January"
$currentYear = date('Y'); // e.g., "2025"

// Get daily sales total
$daily_query = "
    SELECT SUM(o.price * o.quantity) as daily_total 
    FROM orders o 
    JOIN paid p ON o.id = p.order_id 
    WHERE DATE(p.paid_at) = CURDATE()
";
$daily_stmt = $pdo->query($daily_query);
$daily_row = $daily_stmt->fetch(PDO::FETCH_ASSOC);
$daily_total = $daily_row['daily_total'] ?? 0;

// Get weekly sales total
$weekly_query = "
    SELECT SUM(o.price * o.quantity) as weekly_total 
    FROM orders o 
    JOIN paid p ON o.id = p.order_id 
    WHERE WEEK(p.paid_at, 1) = WEEK(CURDATE(), 1) AND YEAR(p.paid_at) = YEAR(CURDATE())
";
$weekly_stmt = $pdo->query($weekly_query);
$weekly_row = $weekly_stmt->fetch(PDO::FETCH_ASSOC);
$weekly_total = $weekly_row['weekly_total'] ?? 0;

// Get monthly sales total
$monthly_query = "
    SELECT SUM(o.price * o.quantity) as monthly_total 
    FROM orders o 
    JOIN paid p ON o.id = p.order_id 
    WHERE MONTH(p.paid_at) = MONTH(CURDATE()) AND YEAR(p.paid_at) = YEAR(CURDATE())
";
$monthly_stmt = $pdo->query($monthly_query);
$monthly_row = $monthly_stmt->fetch(PDO::FETCH_ASSOC);
$monthly_total = $monthly_row['monthly_total'] ?? 0;

// Fetch daily sales data for the current week
$daily_data_query = "
    SELECT DAYNAME(p.paid_at) AS day, SUM(o.price * o.quantity) AS total 
    FROM orders o 
    JOIN paid p ON o.id = p.order_id 
    WHERE WEEK(p.paid_at, 1) = WEEK(CURDATE(), 1) AND YEAR(p.paid_at) = YEAR(CURDATE())
    GROUP BY DAY(p.paid_at)
    ORDER BY p.paid_at
";
$daily_data_stmt = $pdo->query($daily_data_query);
$daily_data = $daily_data_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch weekly sales data for the current month
$weekly_data_query = "
    SELECT WEEK(p.paid_at, 1) AS week, SUM(o.price * o.quantity) AS total 
    FROM orders o 
    JOIN paid p ON o.id = p.order_id 
    WHERE MONTH(p.paid_at) = MONTH(CURDATE()) AND YEAR(p.paid_at) = YEAR(CURDATE())
    GROUP BY WEEK(p.paid_at, 1)
    ORDER BY WEEK(p.paid_at, 1)
";
$weekly_data_stmt = $pdo->query($weekly_data_query);
$weekly_data = $weekly_data_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch monthly sales data for the current year
$monthly_data_query = "
    SELECT MONTHNAME(p.paid_at) AS month, SUM(o.price * o.quantity) AS total 
    FROM orders o 
    JOIN paid p ON o.id = p.order_id 
    WHERE YEAR(p.paid_at) = YEAR(CURDATE())
    GROUP BY MONTH(p.paid_at)
    ORDER BY MONTH(p.paid_at)
";
$monthly_data_stmt = $pdo->query($monthly_data_query);
$monthly_data = $monthly_data_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch paid orders grouped by product name for today
$query = "
    SELECT 
        p.image, 
        p.product_name, 
        SUM(o.quantity) AS total_sold, 
        SUM(o.price * o.quantity) AS total_sales
    FROM orders o
    JOIN paid pd ON o.id = pd.order_id
    JOIN products p ON o.product_id = p.id
    WHERE DATE(pd.paid_at) = CURDATE()
    GROUP BY p.id
";
$stmt = $pdo->prepare($query);
$stmt->execute();
$paid_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>




<!DOCTYPE html>
<html lang="en">
  <head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>PlayFull Bistro Sales Report</title>
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


    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
              <li class="nav-item active">
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
              <li class="nav-item">
                <a data-bs-toggle="collapse" href="#base">
                  <i class="fas fa-layer-group"></i>
                  <p>Account List</p>
                  <span class="caret"></span>
                </a>
                <div class="collapse" id="base">
                  <ul class="nav nav-collapse">
                    <li>
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
                    <span class="notification">4</span>
                  </a>
                  <ul
                    class="dropdown-menu notif-box animated fadeIn"
                    aria-labelledby="notifDropdown"
                  >
                    <li>
                      <div class="dropdown-title">
                        You have 4 new notification
                      </div>
                    </li>
                    <li>
                      <div class="notif-scroll scrollbar-outer">
                        <div class="notif-center">
                          <a href="#">
                            <div class="notif-icon notif-primary">
                              <i class="fa fa-user-plus"></i>
                            </div>
                            <div class="notif-content">
                              <span class="block"> New user registered </span>
                              <span class="time">5 minutes ago</span>
                            </div>
                          </a>
                          <a href="#">
                            <div class="notif-icon notif-success">
                              <i class="fa fa-comment"></i>
                            </div>
                            <div class="notif-content">
                              <span class="block">
                                Rahmad commented on Admin
                              </span>
                              <span class="time">12 minutes ago</span>
                            </div>
                          </a>
                          <a href="#">
                            <div class="notif-img">
                              <img
                                src="../../assets/img/profile2.jpg"
                                alt="Img Profile"
                              />
                            </div>
                            <div class="notif-content">
                              <span class="block">
                                Reza send messages to you
                              </span>
                              <span class="time">12 minutes ago</span>
                            </div>
                          </a>
                          <a href="#">
                            <div class="notif-icon notif-danger">
                              <i class="fa fa-heart"></i>
                            </div>
                            <div class="notif-content">
                              <span class="block"> Farrah liked Admin </span>
                              <span class="time">17 minutes ago</span>
                            </div>
                          </a>
                        </div>
                      </div>
                    </li>
                    <li>
                      <a class="see-all" href="javascript:void(0);"
                        >See all notifications<i class="fa fa-angle-right"></i>
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
    <div class="page-inner mt-3">
        <h3>Sales Report</h3>
        <div class="row">
            <!-- Daily Sales Box -->
            <div class="col-md-4">
                <div class="card text-white bg-primary mb-3">
                    <div class="card-header">Total Sales (Today)</div>
                    <div class="card-body">
                        <h5 class="card-title">₱<?php echo number_format($daily_total, 2); ?></h5>
                    </div>
                </div>
            </div>

            <!-- Weekly Sales Box -->
            <div class="col-md-4">
                <div class="card text-white bg-success mb-3">
                    <div class="card-header">Total Sales (This Week)</div>
                    <div class="card-body">
                        <h5 class="card-title">₱<?php echo number_format($weekly_total, 2); ?></h5>
                    </div>
                </div>
            </div>

            <!-- Monthly Sales Box -->
            <div class="col-md-4">
                <div class="card text-white bg-info mb-3">
                    <div class="card-header">Total Sales (This Month)</div>
                    <div class="card-body">
                        <h5 class="card-title">₱<?php echo number_format($monthly_total, 2); ?></h5>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="row">
            <!-- Daily Sales Bar Chart -->
            <div class="col-lg-4 col-md-6">
                <div class="card mb-3">
                    <div class="card-header">Daily Sales (This Week)</div>
                    <div class="card-body">
                        <canvas id="dailyChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Weekly Sales Bar Chart -->
<div class="col-lg-4 col-md-6">
    <div class="card mb-3">
        <div class="card-header">Weekly Sales (<?php echo $currentMonth; ?>)</div>
        <div class="card-body">
            <canvas id="weeklyChart"></canvas>
        </div>
    </div>
</div>

<!-- Monthly Sales Bar Chart -->
<div class="col-lg-4 col-md-6">
    <div class="card mb-3">
        <div class="card-header">Monthly Sales (<?php echo $currentYear; ?>)</div>
        <div class="card-body">
            <canvas id="monthlyChart"></canvas>
        </div>
    </div>
</div>
        </div>
    </div>
    <div class="container mt-4">
    <h2 class="mb-3">Today's Sales Report</h2>
    <table class="table table-bordered text-center">
        <thead class="table-dark">
            <tr>
                <th>Product Image</th>
                <th>Product Name</th>
                <th>Total Sold</th>
                <th>Total Sales (₱)</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($paid_orders)): ?>
                <?php foreach ($paid_orders as $order): ?>
                    <tr>
                        <td>
                            <img src="<?php echo htmlspecialchars($order['image']); ?>" 
                                 alt="<?php echo htmlspecialchars($order['product_name']); ?>" 
                                 style="width: 60px; height: 60px; object-fit: cover; border-radius: 5px;">
                        </td>
                        <td><?php echo htmlspecialchars($order['product_name']); ?></td>
                        <td><?php echo number_format($order['total_sold']); ?></td>
                        <td>₱<?php echo number_format($order['total_sales'], 2); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" class="text-center text-danger">No sales recorded today.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
</div>



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
    // Pass PHP data to JavaScript
    const dailyData = <?php echo json_encode($daily_data); ?>;
    const weeklyData = <?php echo json_encode($weekly_data); ?>;
    const monthlyData = <?php echo json_encode($monthly_data); ?>;

    // Prepare daily chart data
    const dailyLabels = dailyData.map(item => item.day);
    const dailyTotals = dailyData.map(item => parseFloat(item.total));

    // Prepare weekly chart data
    const weeklyLabels = weeklyData.map(item => `Week ${item.week}`);
    const weeklyTotals = weeklyData.map(item => parseFloat(item.total));

    // Prepare monthly chart data
    const monthlyLabels = monthlyData.map(item => item.month);
    const monthlyTotals = monthlyData.map(item => parseFloat(item.total));

    // Create Daily Sales Chart
    const dailyCtx = document.getElementById('dailyChart').getContext('2d');
    new Chart(dailyCtx, {
        type: 'bar',
        data: {
            labels: dailyLabels,
            datasets: [{
                label: 'Sales (Daily)',
                data: dailyTotals,
                backgroundColor: 'rgba(54, 162, 235, 0.5)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Create Weekly Sales Chart
    const weeklyCtx = document.getElementById('weeklyChart').getContext('2d');
    new Chart(weeklyCtx, {
        type: 'bar',
        data: {
            labels: weeklyLabels,
            datasets: [{
                label: 'Sales (Weekly)',
                data: weeklyTotals,
                backgroundColor: 'rgba(75, 192, 192, 0.5)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Create Monthly Sales Chart
    const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
    new Chart(monthlyCtx, {
        type: 'bar',
        data: {
            labels: monthlyLabels,
            datasets: [{
                label: 'Sales (Monthly)',
                data: monthlyTotals,
                backgroundColor: 'rgba(153, 102, 255, 0.5)',
                borderColor: 'rgba(153, 102, 255, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>

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
