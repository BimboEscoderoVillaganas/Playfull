<?php

// Database connection
include '../../src/db/db_connection.php';
// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login.php if no user is logged in
    header('Location: ../../login.php');
    exit();
}

// Get the logged-in user's name
$username = htmlspecialchars($_SESSION['username']);
// Get the logged-in user's email
$useremail = htmlspecialchars($_SESSION['email']);

// Get the current page name
$current_page = basename($_SERVER['PHP_SELF']);

// Fetch active products from the database using PDO
$query = "SELECT id, product_name, price, image FROM products WHERE status = 'active'";
$stmt = $pdo->query($query);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Check if the logged-in user is an admin
if ($_SESSION['user_type'] !== 'admin') {
  // Redirect unauthorized users to the homepage or an error page
  header('Location: 403.php'); // Use 403 Forbidden error page
  exit();
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
    <title>PlayFull Bistro add order</title>
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
              <li class="nav-item active">
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
        <h2 class="mt-5">Add Order</h2>
        <div class="row">
            <!-- Left column: Display all products -->
            <div class="col-md-6" style="max-height: 400px; overflow-y: auto;">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h3>Products</h3>
    </div>
    <ul class="list-group" id="productList">
        <?php foreach ($products as $product): ?>
            <li class="list-group-item d-flex justify-content-between align-items-center product-item">
                <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="Product Image" style="width: 50px; height: 50px; margin-right: 10px;">
                <span class="product-name"><?php echo htmlspecialchars($product['product_name']); ?></span> - 
                ₱<?php echo htmlspecialchars($product['price']); ?>
                <button class="btn btn-primary btn-sm" 
                    onclick="selectProduct(<?php echo $product['id']; ?>, 
                                           '<?php echo htmlspecialchars($product['product_name']); ?>', 
                                           <?php echo $product['price']; ?>, 
                                           '<?php echo htmlspecialchars($product['image']); ?>')">
                    Select
                </button>
            </li>
        <?php endforeach; ?>
    </ul>
</div>
            <!-- Right column: Display selected products with quantity input -->
            <div class="col-md-6" style="position: sticky; top: 0;">
                <h3>Selected Products</h3>
                <form id="orderForm" action="submit_order.php" method="POST">
                    <div class="form-group d-flex justify-content-between align-items-center">
                        <div>
                            <label for="orderNumber">Order Number/Name</label>
                            <input type="text" id="orderNumber" name="order_number" class="form-control" required>
                        </div>
                        <div id="totalAmount" class="ml-3 mt-4">
                            <span style="font-weight: bold; font-size: 12px;">Total Amount: </span>
                            <span id="totalAmountValue" style="font-weight: bold; font-size: 12px; color: red;">₱0.00</span>
                        </div>
                    </div>
                    <div id="selectedProducts"></div>
                    <button type="submit" class="btn btn-success mt-3">Submit Order</button>
                </form>
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
      

      // Select product and add to order form
      function selectProduct(id, name, price, image) {
    var selectedProductsDiv = document.getElementById('selectedProducts');
    var productDiv = document.createElement('div');
    productDiv.className = 'selected-product mb-3';
    productDiv.innerHTML = `
        <div class="d-flex justify-content-between align-items-center">
            <img src="${image}" alt="Product Image" style="width: 50px; height: 50px; margin-right: 10px;">
            <span>${name} - ₱${price}</span>
            <input type="hidden" name="product_ids[]" value="${id}">
            <input type="hidden" name="product_prices[]" value="${price}">
            <input type="hidden" name="product_names[]" value="${name}">
            <input type="number" name="quantities[]" class="form-control ml-2" placeholder="Quantity" required oninput="updateTotalAmount()">
            <button type="button" class="btn btn-danger btn-sm ml-2" onclick="removeProduct(this)">Remove</button>
        </div>
    `;
    selectedProductsDiv.appendChild(productDiv);
    updateTotalAmount();
}


function removeProduct(button, productId) {
    if (confirm('Are you sure you want to remove this product from the selection?')) {
        const productDiv = button.closest('.selected-product');
        productDiv.remove();
        updateTotalAmount();
    }
}
function updateTotalAmount() {
    var totalAmount = 0;
    var quantities = document.getElementsByName('quantities[]');
    var prices = document.getElementsByName('product_prices[]');

    for (var i = 0; i < quantities.length; i++) {
        var quantity = parseFloat(quantities[i].value);
        var price = parseFloat(prices[i].value);
        if (!isNaN(quantity) && !isNaN(price)) {
            totalAmount += quantity * price;
        }
    }

    document.getElementById('totalAmountValue').innerText = '₱' + totalAmount.toFixed(2);
}
    </script>
    <script>
    // Display alert if there is a message in the URL
    const urlParams = new URLSearchParams(window.location.search);
    const message = urlParams.get('message');
    const type = urlParams.get('type');

    if (message) {
        // Show alert based on the type
        alert(message);

        // Optionally, you can clear the query parameters after displaying the alert
        window.history.replaceState({}, document.title, window.location.pathname);
    }
</script>

    <!-- Logout confirmation dialog -->
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
    <script src="assets/js/plugin/jsvectormap/world.js"></script>

    <!-- Sweet Alert -->
    <script src="../../assets/js/plugin/sweetalert/sweetalert.min.js"></script>

    <!-- Kaiadmin JS -->
    <script src="../../assets/js/kaiadmin.min.js"></script>

  </body>
</html>
