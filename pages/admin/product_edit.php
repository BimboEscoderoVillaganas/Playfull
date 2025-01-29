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
// Fetch the product details from the database
$product_id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle form submission
$message = '';
$success = false;
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $productName = $_POST['product_name'];
    $description = $_POST['description'];
    $quantity = $_POST['quantity'];
    $price = $_POST['price'];
    $image = $_FILES['image'];
    $existingImage = $product['image'];

    // Handle image upload
    $targetDir = "product_img/";
    $targetFile = $targetDir . basename($image["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

    if (!empty($image["name"])) {
        // Check if image file is a actual image or fake image
        $check = getimagesize($image["tmp_name"]);
        if ($check !== false) {
            $uploadOk = 1;
        } else {
            $message = "File is not an image.";
            $uploadOk = 0;
        }

        // Check file size
        if ($image["size"] > 500000) {
            $message = "Sorry, your file is too large.";
            $uploadOk = 0;
        }

        // Allow certain file formats
        if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" && $imageFileType != "webp") {
            $message = "Sorry, only JPG, JPEG, PNG, GIF & WEBP files are allowed.";
            $uploadOk = 0;
        }

        // Check if $uploadOk is set to 0 by an error
        if ($uploadOk == 0) {
            $message = "Sorry, your file was not uploaded.";
        // if everything is ok, try to upload file
        } else {
            if (move_uploaded_file($image["tmp_name"], $targetFile)) {
                $message = "The file ". htmlspecialchars(basename($image["name"])). " has been uploaded.";

                // Delete the old image file if it exists
                if (!empty($existingImage) && file_exists($existingImage)) {
                    unlink($existingImage);
                }

                // Update product details in the database
                $stmt = $pdo->prepare("UPDATE products SET image = ?, product_name = ?, description = ?, quantity = ?, price = ? WHERE id = ?");
                if ($stmt) {
                    $stmt->execute([$targetFile, $productName, $description, $quantity, $price, $product_id]);

                    if ($stmt->rowCount() > 0) {
                        $message = "Product updated successfully.";
                        $success = true;
                    } else {
                        $message = "Error: " . $stmt->errorInfo()[2];
                    }
                } else {
                    $message = "Error: Failed to prepare the SQL statement.";
                }
            } else {
                $message = "Sorry, there was an error uploading your file.";
            }
        }
    } else {
        // Update product details without changing the image
        $stmt = $pdo->prepare("UPDATE products SET product_name = ?, description = ?, quantity = ?, price = ? WHERE id = ?");
        if ($stmt) {
            $stmt->execute([$productName, $description, $quantity, $price, $product_id]);

            if ($stmt->rowCount() > 0) {
                $message = "Product updated successfully.";
                $success = true;
            } else {
                $message = "Error: " . $stmt->errorInfo()[2];
            }
        } else {
            $message = "Error: Failed to prepare the SQL statement.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Edit Product</title>
    <meta content="width=device-width, initial-scale=1.0, shrink-to-fit=no" name="viewport" />
    <link rel="icon" href="../../assets/img/logo.webp" type="image/x-icon" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.5/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="../../assets/css/plugins.min.css" />
    <link rel="stylesheet" href="../../assets/css/kaiadmin.min.css" />
    <style>
        body {
            background-color: #f8f9fa;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .card {
            width: 100%;
            max-width: 600px;
            margin-top: 50px;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .form-label {
            font-weight: bold;
        }
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }
        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #004085;
        }
        .alert-info {
            margin-top: 20px;
        }
        .img-preview {
            max-width: 100px;
            max-height: 100px;
            object-fit: cover;
        }
    </style>
    <script>
        let formChanged = false;

        document.addEventListener('DOMContentLoaded', (event) => {
            const form = document.querySelector('form');
            const inputs = form.querySelectorAll('input, textarea');

            inputs.forEach(input => {
                input.addEventListener('change', () => {
                    formChanged = true;
                });
            });

            window.addEventListener('beforeunload', (event) => {
                if (formChanged) {
                    event.preventDefault();
                    event.returnValue = '';
                }
            });

            form.addEventListener('submit', () => {
                formChanged = false;
            });
        });

        <?php if (!empty($message)): ?>
            alert('<?php echo $message; ?>');
            <?php if ($success): ?>
                window.location.href = 'products.php';
            <?php endif; ?>
        <?php endif; ?>
    </script>
</head>
<body>
    <div class="card">
        <h1>Edit Product</h1>
        <form action="product_edit.php?id=<?php echo $product_id; ?>" method="post" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="image" class="form-label">Product Image</label>
                <input type="file" class="form-control" id="image" name="image">
                <?php if (!empty($product['image'])): ?>
                    <img src="<?php echo htmlspecialchars($product['image']); ?>" class="img-fluid img-preview mt-2" alt="<?php echo htmlspecialchars($product['product_name']); ?>">
                <?php endif; ?>
            </div>
            <div class="mb-3">
                <label for="product_name" class="form-label">Product Name</label>
                <input type="text" class="form-control" id="product_name" name="product_name" value="<?php echo htmlspecialchars($product['product_name']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description" rows="3" required><?php echo htmlspecialchars($product['description']); ?></textarea>
            </div>
            <div class="mb-3">
                <label for="quantity" class="form-label">Quantity</label>
                <input type="number" class="form-control" id="quantity" name="quantity" value="<?php echo htmlspecialchars($product['quantity']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="price" class="form-label">Price</label>
                <input type="number" step="0.01" class="form-control" id="price" name="price" value="<?php echo htmlspecialchars($product['price']); ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Update Product</button>
        </form>
    </div>
</body>
</html>