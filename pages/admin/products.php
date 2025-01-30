<?php
// Include the database connection file
require_once '../../src/db/db_connection.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login.php if no user is logged in
    header('Location: ../../login.php');
    exit();
}

// Check if the logged-in user is an admin
if ($_SESSION['user_type'] !== 'admin') {
  // Redirect unauthorized users to the homepage or an error page
  header('Location: 403.php'); // Use 403 Forbidden error page
  exit();
}

// Get the logged-in user's email
$useremail = htmlspecialchars($_SESSION['email']);
// Fetch the username from the database
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$username = $user['username'] ?? '';

// Fetch only active products from the database
$stmt = $pdo->prepare("SELECT * FROM products WHERE status = 'active'");
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);


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
    <title>PlayFull Bistro Products</title>
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
        .card-img-top {
            height: 200px;
            object-fit: cover;
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
              <li class="nav-item active">
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
        <h2 class="mt-5">All Products</h2>
        <div class="row">
            <?php foreach ($products as $product): ?>
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <?php if (!empty($product['image'])): ?>
                            <img src="<?php echo htmlspecialchars($product['image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($product['product_name']); ?>">
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($product['product_name']); ?></h5>
                            <p class="card-text"><?php echo htmlspecialchars($product['description']); ?></p>
                            <p class="card-text"><strong>Quantity:</strong> <?php echo htmlspecialchars($product['quantity']); ?></p>
                            <p class="card-text"><strong>Price:</strong> ₱<?php echo htmlspecialchars($product['price']); ?></p>
                            <a href="product_edit.php?id=<?php echo $product['id']; ?>" class="btn btn-primary" style="font-size: 12px;">Edit</a>
                            <button class="btn btn-secondary" onclick="showAddQuantityModal(<?php echo $product['id']; ?>, <?php echo $product['quantity']; ?>)" style="font-size: 12px;">
                                Add Quantity
                            </button>
                            <button class="btn btn-danger" onclick="confirmDelete(<?php echo $product['id']; ?>, '<?php echo htmlspecialchars($product['image']); ?>')" style="font-size: 12px;">Delete</button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="addQuantityModal" tabindex="-1" aria-labelledby="addQuantityModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addQuantityModalLabel">Add Quantity</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addQuantityForm">
                    <input type="hidden" name="product_id" id="product_id">
                    <div class="mb-3">
                        <label for="existing_quantity" class="form-label">Existing Quantity</label>
                        <input type="number" class="form-control" id="existing_quantity" disabled>
                    </div>
                    <div class="mb-3">
                        <label for="add_quantity" class="form-label">Add Quantity</label>
                        <input type="number" class="form-control" id="add_quantity" name="add_quantity" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="saveQuantity()">Save</button>
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


    
    <!-- jQuery Scrollbar -->
<script>
        document.getElementById('product_name').addEventListener('input', function() {
            document.getElementById('preview_product_name').textContent = this.value;
        });

        document.getElementById('description').addEventListener('input', function() {
            document.getElementById('preview_description').textContent = this.value;
        });

        document.getElementById('quantity').addEventListener('input', function() {
            document.getElementById('preview_quantity').textContent = this.value;
        });

        document.getElementById('price').addEventListener('input', function() {
            document.getElementById('preview_price').textContent = parseFloat(this.value).toFixed(2);
        });

        document.getElementById('image').addEventListener('change', function() {
            const [file] = this.files;
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('preview_image').src = e.target.result;
                    document.getElementById('preview_image').style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });

        // Confirmation before leaving the page
        const form = document.getElementById('productForm');
        const submitBtn = document.getElementById('submitBtn');
        let isFormDirty = false;

        form.addEventListener('input', (event) => {
            if (event.target !== submitBtn) {
                isFormDirty = true;
            }
        });

        submitBtn.addEventListener('click', () => {
            isFormDirty = false;
        });

        window.addEventListener('beforeunload', (e) => {
            if (isFormDirty) {
                e.preventDefault();
                e.returnValue = '';
            }
        });

       //Delete confirmation
    function confirmDelete(productId, productImage) {
        if (confirm('Are you sure you want to delete this product?')) {
            window.location.href = 'product_delete.php?id=' + productId + '&image=' + encodeURIComponent(productImage);
        }
    }


    function showAddQuantityModal(productId, existingQuantity) {
    document.getElementById('product_id').value = productId;
    document.getElementById('existing_quantity').value = existingQuantity;
    document.getElementById('add_quantity').value = '';
    const modal = new bootstrap.Modal(document.getElementById('addQuantityModal'));
    modal.show();
}

function saveQuantity() {
    const formData = new FormData(document.getElementById('addQuantityForm'));
    fetch('update_quantity.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Quantity updated successfully!');
            location.reload();
        } else {
            alert('Failed to update quantity: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while updating the quantity.');
    });
}
    </script>
    <!-- Logout confirmation -->
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
