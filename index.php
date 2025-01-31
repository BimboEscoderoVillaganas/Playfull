<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="assets/img/logo.webp" type="image/x-icon">
    <title>PlayFullBistro</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap">
    <link rel="stylesheet" href="assets/css/index.css">
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="logo-container">
                <img src="assets/img/logo.webp" alt="PlayFullBistro Logo" class="logo-img" style="margin:  0px 20px 0px -20px">
            </div>
            <nav>
                <ul class="nav-links"><!--
                    <li><a href="#menu">Menu</a></li>
                    <li><a href="#about">About</a></li>
                    <li><a href="#contact">Contact</a></li>-->
                    <li><a href="login.php" class="login-btn"style="margin:  -10px 0px 0px 0px">Login</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <section class="hero">
        <div class="hero-content">
            <h1>Welcome to PlayFull Bistro</h1>
            <p>Experience the best BBQ and mouth-watering food in town!</p>
            <a href="#menu" class="btn">Explore Our Menu</a>
        </div>
    </section>

    <section id="menu" class="menu-section">
        <div class="container">
            <h2>Our Menu</h2>
            <div class="menu-grid">
                <?php
                // Include the database connection file
                require 'src/db/db_connection.php';

                // Fetch products from database
                $sql = "SELECT product_name, description, price, image FROM products";
                $stmt = $pdo->prepare($sql);
                $stmt->execute();
                $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if ($products) {
                    // Output data of each row
                    foreach ($products as $product) {
                        echo '<div class="menu-item">';
                        echo '<img src="pages/admin/' . htmlspecialchars($product["image"]) . '" alt="' . htmlspecialchars($product["product_name"]) . '">';
                        echo '<h3>' . htmlspecialchars($product["product_name"]) . '</h3>';
                        echo '<p>' . htmlspecialchars($product["description"]) . '</p>';
                        echo '<h4>Price: <span style="color:green; text-decoration: underline;">₱' . htmlspecialchars($product["price"]) . '</span></h4>';
                        echo '</div>';
                    }
                } else {
                    echo "No products found.";
                }
                ?>
            </div>
        </div>
    </section>

    <section id="about" class="about-section">
        <div class="container">
            <h2>About Us</h2>
            <p>At PlayFullBistro, we bring people together with good food, great vibes, and unforgettable experiences. Whether you're craving BBQ or something sweet, we’ve got you covered!</p>
            <div class="about-images">
                <img src="assets/img/model1.webp" alt="model 1">
                <img src="assets/img/model2.webp" alt="model 2">
                <img src="assets/img/model3.webp" alt="model 3">
            </div>
        </div>
    </section>

    <section id="contact" class="contact-section">
        <div class="container">
            <h2>Contact Us</h2>
            <p>Have questions or want to order? Get in touch!</p>
            <p>Email: <a href="mailto:info@playfullbistro.com">info@playfullbistro.com</a></p>
            <p>Phone: <a href="tel:+1234567890">+1 234 567 890</a></p>
        </div>
    </section>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2024 PlayFullBistro. All Rights Reserved.</p>
        </div>
    </footer>
</body>
</html>