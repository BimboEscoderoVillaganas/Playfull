<?php 
// Include database connection
include '../../src/db/db_connection.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit();
}

// Check if the logged-in user is an admin
if ($_SESSION['user_type'] !== 'admin') {
    header('Location: 403.php');
    exit();
}

// Get the logged-in user's name
$username = htmlspecialchars($_SESSION['username']);
// Get the logged-in user's email
$useremail = htmlspecialchars($_SESSION['email']);
// Get today's date
$today = date('Y-m-d');

// Get the current date's weekday (0 for Sunday, 6 for Saturday)
$weekday = date('w', strtotime($today));

// Calculate the start and end of the week (Sunday to Monday)
$startOfWeek = date('Y-m-d', strtotime("-$weekday days"));
$endOfWeek = date('Y-m-d', strtotime("+" . (6 - $weekday) . " days"));

// Fetch weekly sales data (from Sunday to Monday)
$weeklySalesQuery = "SELECT DATE(pa.paid_at) AS sale_date, SUM(o.quantity * o.price) AS total_sales
                     FROM paid pa
                     JOIN orders o ON pa.order_id = o.id
                     WHERE DATE(pa.paid_at) BETWEEN ? AND ?
                     GROUP BY sale_date
                     ORDER BY sale_date ASC";

$weeklyStmt = $pdo->prepare($weeklySalesQuery);
$weeklyStmt->execute([$startOfWeek, $endOfWeek]);
$weeklySalesData = $weeklyStmt->fetchAll(PDO::FETCH_ASSOC);

// Process weekly sales data
$weeklyLabels = [];
$weeklySales = [];

for ($i = 0; $i <= 6; $i++) {
    $date = date('Y-m-d', strtotime("+$i days", strtotime($startOfWeek)));
    $weeklyLabels[] = date('M d', strtotime($date));
    $salesFound = false;

    foreach ($weeklySalesData as $data) {
        if ($data['sale_date'] === $date) {
            $weeklySales[] = $data['total_sales'];
            $salesFound = true;
            break;
        }
    }
    if (!$salesFound) {
        $weeklySales[] = 0;
    }
}

// Fetch today's product sales data
$productSalesQuery = "SELECT p.product_name, SUM(o.quantity) AS total_sold 
                      FROM products p
                      JOIN orders o ON p.id = o.product_id
                      JOIN paid pa ON o.id = pa.order_id
                      WHERE DATE(pa.paid_at) = ?
                      GROUP BY p.product_name";

$productStmt = $pdo->prepare($productSalesQuery);
$productStmt->execute([$today]);
$productSalesData = $productStmt->fetchAll(PDO::FETCH_ASSOC);

// Process product sales data
$productLabels = [];
$productSales = [];

foreach ($productSalesData as $data) {
    $productLabels[] = $data['product_name'];
    $productSales[] = $data['total_sold'];
}

// Fetch remaining stock data excluding inactive products
$stockQuery = "SELECT p.product_name, p.quantity, 
                      (p.quantity - COALESCE(SUM(o.quantity), 0)) AS remaining_stock
               FROM products p
               LEFT JOIN orders o ON p.id = o.product_id
               LEFT JOIN paid pa ON o.id = pa.order_id AND DATE(pa.paid_at) = ?
               WHERE p.status = 'active'
               GROUP BY p.id";

$stockStmt = $pdo->prepare($stockQuery);
$stockStmt->execute([$today]);
$stockData = $stockStmt->fetchAll(PDO::FETCH_ASSOC);

$minThreshold = 50; // Set threshold for restocking alert

// Process stock data
$stockLabels = [];
$stockRemaining = [];
$threshold = [];
$lowStockProducts = [];

foreach ($stockData as $stock) {
    $stockLabels[] = $stock['product_name'];
    $stockRemaining[] = max(0, $stock['remaining_stock']);
    $threshold[] = $minThreshold; // Constant threshold line

    if ($stock['remaining_stock'] < $minThreshold) {
        $lowStockProducts[] = "<strong>{$stock['product_name']}</strong> is low on stock! Only <strong>{$stock['remaining_stock']}</strong> left.";
    }
}

// Convert data to JSON for JavaScript
$weeklyLabelsJSON = json_encode($weeklyLabels);
$weeklySalesJSON = json_encode($weeklySales);
$productLabelsJSON = json_encode($productLabels);
$productSalesJSON = json_encode($productSales);
$stockLabelsJSON = json_encode($stockLabels);
$stockRemainingJSON = json_encode($stockRemaining);
$thresholdJSON = json_encode($threshold);
$lowStockProductsJSON = json_encode($lowStockProducts);
?>





<!DOCTYPE html>
<html lang="en">
  <head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>PlayFull Bistro Admin Dashboard</title>
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
  <style>
.chart-container {
    width: 100%;
    height: 200px;
}
</style>
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
              <li class="nav-item active">
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
      <a class="see-all" href="sales.php">
        View all
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

    <!-- Charts Container -->
    <div class="row">
        <!-- Point Styling Chart (Weekly Sales) -->
        <div class="col-md-6">
            <div class="card shadow-lg mt-4">
                <div class="card-body">
                    <h4 class="card-title text-center">Weekly Sales Overview</h4>
                    <div class="chart-container">
                        <canvas id="salesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Doughnut Chart (Product Sales Breakdown) -->
        <div class="col-md-6">
            <div class="card shadow-lg mt-4">
                <div class="card-body">
                    <h4 class="card-title text-center">Today's Product Sales</h4>
                    <div class="chart-container">
                        <canvas id="productSalesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stacked Bar/Line Chart (Remaining Stock) -->
    <div class="row" style="margin:0px 20px;">
        <div class="col-md-12">
            <div class="card shadow-lg mt-4">
                <div class="card-body">
                    <h4 class="card-title text-center">Remaining Product Stock</h4>
                    <div class="chart-container1">
                        <canvas id="remainingStockChart"></canvas>
                    </div>
                    <div id="restockMessage" class="alert alert-warning mt-3" style="display: none;"></div>
                </div>
            </div>
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


















    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    // Weekly Sales Chart
    new Chart(document.getElementById("salesChart").getContext("2d"), {
        type: "line",
        data: {
            labels: <?php echo $weeklyLabelsJSON; ?>,
            datasets: [{
                label: "Total Sales",
                data: <?php echo $weeklySalesJSON; ?>,
                borderColor: "#007BFF",
                backgroundColor: "rgba(0, 123, 255, 0.2)",
                borderWidth: 2,
                pointStyle: "circle",
                pointRadius: 5,
                pointHoverRadius: 7
            }]
        },
        options: { responsive: true, maintainAspectRatio: false }
    });

    // Product Sales Chart
    new Chart(document.getElementById("productSalesChart").getContext("2d"), {
        type: "doughnut",
        data: {
            labels: <?php echo $productLabelsJSON; ?>,
            datasets: [{
                data: <?php echo $productSalesJSON; ?>,
                backgroundColor: ["#FF6384", "#36A2EB", "#FFCE56", "#4CAF50", "#9C27B0"],
                hoverOffset: 4
            }]
        },
        options: { responsive: true, maintainAspectRatio: false }
    });

    // Remaining Stock Chart
    new Chart(document.getElementById("remainingStockChart").getContext("2d"), {
        type: "bar",
        data: {
            labels: <?php echo $stockLabelsJSON; ?>,
            datasets: [
                {
                    label: "Remaining Stock",
                    data: <?php echo $stockRemainingJSON; ?>,
                    backgroundColor: "#FF5733"
                },
                {
                    label: "Restock Threshold",
                    data: <?php echo $thresholdJSON; ?>,
                    type: "line",
                    borderColor: "#36A2EB",
                    borderWidth: 2,
                    pointRadius: 0,
                    fill: false
                }
            ]
        }
    });

    if (<?php echo json_encode($lowStockProducts); ?>.length > 0) {
        document.getElementById("restockMessage").innerHTML = <?php echo json_encode($lowStockProducts); ?>.join("<br>");
        document.getElementById("restockMessage").style.display = "block";
    }
});
</script>

  </body>
</html>
