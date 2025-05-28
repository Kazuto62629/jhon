<?php
// database.php should contain the PDO connection
require_once 'Includes/database.php';
require_once("Includes/logger.php");

$query = "SELECT * FROM banners";
$stmt = $pdo->prepare($query);
$stmt->execute();
$bannerData = $stmt->fetchAll(PDO::FETCH_ASSOC);
// $logger->info("Fetched banners from database", $bannerData);

// Delete Menu Item
if (isset($_GET['delete_menu_item'])) {
    $id = $_GET['delete_menu_item'];
    $stmt = $pdo->prepare("DELETE FROM menu_items WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();

    header("Location: admin_dashboard.php?page=menu_items");
}

// Delete product
if (isset($_GET['delete_product'])) {
    $id = $_GET['delete_product'];

    $logger->info("Deleting product: " . $id);

    $stmt = $pdo->prepare("DELETE FROM products WHERE id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        $logger->info("Product $id deleted successfully.");
    } else {
        $logger->error("Failed to delete product $id.");
    }

    header("Location: admin_dashboard.php?page=products");
    exit();
}

// Delete Homepage Banner
if (isset($_GET['delete_banner'])) {
    $id = $_GET['delete_banner'];
    $stmt = $pdo->prepare("DELETE FROM banners WHERE id = :id");
    $stmt->bindParam(':id', $id);

    if ($stmt->execute()) {
        $logger->info("Banner $id deleted successfully.");
    } else {
        $logger->error("Failed to delete banner $id.");
    }

    header("Location: admin_dashboard.php?page=homepage_banners");
    exit();
}

// Edit Homepage Banner
if (isset($_POST['edit_banner'])) {
    $id = $_POST['banner_id'];
    $logger->info("Editing banner: " . $id  );
    $title = $_POST['banner_title'];
    $description = $_POST['banner_description'];

    // Initialize the image URL with the existing image URL
    $image_path = isset($banner['image_path']) ? $bannerData['image_path'] : null;

    // Handle file upload
    if (isset($_FILES['banner_image']) && $_FILES['banner_image']['error'] == UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['banner_image']['tmp_name'];
        $fileName = $_FILES['banner_image']['name'];
        $fileSize = $_FILES['banner_image']['size'];
        $fileType = $_FILES['banner_image']['type'];

        // Specify the directory where the image will be saved
        $uploadFileDir = './images/';
        $dest_path = $uploadFileDir . $fileName;

        // Move the file to the specified directory
        if (move_uploaded_file($fileTmpPath, $dest_path)) {
            $image_path = $dest_path;
        }
    }

    // Update banner in the database
    $stmt = $pdo->prepare("UPDATE banners SET title = :title, description = :description, image_path = :image_path WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->bindParam(':title', $title);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':image_path', $image_path);
    $stmt->execute();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Add product
    if (isset($_POST['add_product'])) {
        $name = $_POST['product_name'];
        $description = $_POST['product_description'];
        $price = $_POST['product_price'];
        $stock = $_POST['product_stock'];

        $uploadOk = true;
        $errorMsg = '';

        // Allowed image types and max size (2MB)
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        $maxSize = 2 * 1024 * 1024; // 2MB

        if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] == UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['product_image']['tmp_name'];
            $fileName = $_FILES['product_image']['name'];
            $fileSize = $_FILES['product_image']['size'];
            $fileType = $_FILES['product_image']['type'];
            $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            // Validate file type
            if (!in_array($fileType, $allowedTypes)) {
                $uploadOk = false;
                $errorMsg = "Only JPG, PNG, or WEBP files are allowed.";
            }

            // Validate file size
            if ($fileSize > $maxSize) {
                $uploadOk = false;
                $errorMsg = "File size must be less than 2MB.";
            }

            // Sanitize file name and generate a unique one
            $safeFileName = uniqid('img_', true) . '.' . $fileExt;

            // Destination path
            $uploadFileDir = './images/';
            $dest_path = $uploadFileDir . $safeFileName;

            if ($uploadOk) {
                if (move_uploaded_file($fileTmpPath, $dest_path)) {
                    // Insert product into the database
                    $stmt = $pdo->prepare("INSERT INTO products (name, description, price, stock, images) VALUES (:name, :description, :price, :stock, :images)");
                    $stmt->bindParam(':name', $name);
                    $stmt->bindParam(':description', $description);
                    $stmt->bindParam(':price', $price);
                    $stmt->bindParam(':stock', $stock);
                    $stmt->bindParam(':images', $dest_path);
                    $stmt->execute();
                } else {
                    // File failed to move
                    $errorMsg = "Failed to move uploaded file.";
                }
            }

        } else {
            $uploadOk = false;
            $errorMsg = "No file uploaded or upload error.";
        }

        if (!$uploadOk) {
            // Optionally log error or display
            echo "<script>alert('Upload failed: $errorMsg'); window.history.back();</script>";
            exit();
        }

        header("Location: admin_dashboard.php?page=products");
        exit();
    }


    // Edit product
    if (isset($_POST['edit_product'])) {
        $id = $_POST['product_id'];
        $name = $_POST['product_name'];
        $description = $_POST['product_description'];
        $price = $_POST['product_price'];
        $stock = $_POST['product_stock'];

        // Initialize the image URL with the existing image URL
        $images = isset($product['images']) ? $product['images'] : null;

        // Handle file upload
        if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] == UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['product_image']['tmp_name'];
            $fileName = $_FILES['product_image']['name'];
            $fileSize = $_FILES['product_image']['size'];
            $fileType = $_FILES['product_image']['type'];

            // Specify the directory where the image will be saved
            $uploadFileDir = './images/';
            $dest_path = $uploadFileDir . $fileName;

            // Move the file to the specified directory
            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                $images = $dest_path; // Update the image URL with the new path
            }
        }

        // Update product in the database
        $stmt = $pdo->prepare("UPDATE products SET name = :name, description = :description, price = :price, stock = :stock, images = :images WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':stock', $stock);
        $stmt->bindParam(':images', $images); // Save the path to the image
        $stmt->execute();

        header("Location: admin_dashboard.php?page=products");
    }

   


    // Add Homepage Banner
    if (isset($_POST['add_banner'])) {
        $title = $_POST['banner_title'];
        $description = $_POST['banner_description'];

        // Handle file upload
        if (isset($_FILES['banner_image']) && $_FILES['banner_image']['error'] == UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['banner_image']['tmp_name'];
            $fileName = $_FILES['banner_image']['name'];
            $fileSize = $_FILES['banner_image']['size'];
            $fileType = $_FILES['banner_image']['type'];

            // Specify the directory where the image will be saved
            $uploadFileDir = './images/';
            $dest_path = $uploadFileDir . $fileName;

            // Move the file to the specified directory
            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                // Insert banner into the database
                $stmt = $pdo->prepare("INSERT INTO banners (title, description, image_path) VALUES (:title, :description, :image_path)");
                $stmt->bindParam(':title', $title);
                $stmt->bindParam(':description', $description);
                $stmt->bindParam(':image_path', $dest_path);
                $stmt->execute();
            }
        }

        header("Location: admin_dashboard.php?page=homepage_banners");
    }

    // Add Menu Item
    if (isset($_POST['add_menu_item'])) {
        $name = $_POST['menu_item_name'];
        $description = $_POST['menu_item_description'];
        $price = $_POST['menu_item_price'];
        $category = $_POST['menu_item_category'];
        $is_featured = isset($_POST['menu_item_is_featured']) ? 1 : 0;

        // Handle file upload
        if (isset($_FILES['menu_item_image']) && $_FILES['menu_item_image']['error'] == UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['menu_item_image']['tmp_name'];
            $fileName = $_FILES['menu_item_image']['name'];
            $fileSize = $_FILES['menu_item_image']['size'];
            $fileType = $_FILES['menu_item_image']['type'];

            // Specify the directory where the image will be saved
            $uploadFileDir = './images/';
            $dest_path = $uploadFileDir . $fileName;

            // Move the file to the specified directory
            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                // Insert menu item into the database
                $stmt = $pdo->prepare("INSERT INTO menu_items (name, description, price, image_path, category) VALUES (:name, :description, :price, :image_path, :category)");
                $stmt->bindParam(':name', $name);
                $stmt->bindParam(':description', $description);
                $stmt->bindParam(':price', $price);
                $stmt->bindParam(':image_path', $dest_path);
                $stmt->bindParam(':category', $category);
                $stmt->execute();
            }
        }

        header("Location: admin_dashboard.php?page=menu_items");
    }
        $query = "SELECT * FROM menu_items WHERE is_featured = TRUE AND is_active = TRUE LIMIT 4";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $menu_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // Edit Menu Item
    if (isset($_POST['edit_menu_item'])) {
        $id = $_POST['menu_item_id'];
        $name = $_POST['menu_item_name'];
        $description = $_POST['menu_item_description'];
        $price = $_POST['menu_item_price'];
        $category = $_POST['menu_item_category'];

        // Initialize the image URL with the existing image URL
        $image_path = isset($selec['image_path']) ? $menu_item['image_path'] : null;

        // Handle file upload
        if (isset($_FILES['menu_item_image']) && $_FILES['menu_item_image']['error'] == UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['menu_item_image']['tmp_name'];
            $fileName = $_FILES['menu_item_image']['name'];
            $fileSize = $_FILES['menu_item_image']['size'];
            $fileType = $_FILES['menu_item_image']['type'];

            // Specify the directory where the image will be saved
            $uploadFileDir = './images/';
            $dest_path = $uploadFileDir . $fileName;

            // Move the file to the specified directory
            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                $image_path = $dest_path;
            }
        }

        // Update menu item in the database
        $stmt = $pdo->prepare("UPDATE menu_items SET name = :name, description = :description, price = :price, image_path = :image_path, category = :category WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':image_path', $image_path);
        $stmt->bindParam(':category', $category);
        $stmt->execute();

        header("Location: admin_dashboard.php?page=menu_items");
    }

    

    // Add About Us
    if (isset($_POST['add_about_us'])) {
        $title = $_POST['about_us_title'];
        $description = $_POST['about_us_description'];

        // Handle file upload
        if (isset($_FILES['about_us_image']) && $_FILES['about_us_image']['error'] == UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['about_us_image']['tmp_name'];
            $fileName = $_FILES['about_us_image']['name'];
            $fileSize = $_FILES['about_us_image']['size'];
            $fileType = $_FILES['about_us_image']['type'];

            // Specify the directory where the image will be saved
            $uploadFileDir = './images/';
            $dest_path = $uploadFileDir . $fileName;

            // Move the file to the specified directory
            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                // Insert about us into the database
                $stmt = $pdo->prepare("INSERT INTO about_us (title, description, image_path) VALUES (:title, :description, :image_path)");
                $stmt->bindParam(':title', $title);
                $stmt->bindParam(':description', $description);
                $stmt->bindParam(':image_path', $dest_path);
                $stmt->execute();
            }
        }
    }

    // Edit About Us
    if (isset($_POST['edit_about_us'])) {
        $id = $_POST['about_us_id'];
        $title = $_POST['about_us_title'];
        $description = $_POST['about_us_description'];

        // Initialize the image URL with the existing image URL
        $image_path = isset($about_us['image_path']) ? $about_us['image_path'] : null;

        // Handle file upload
        if (isset($_FILES['about_us_image']) && $_FILES['about_us_image']['error'] == UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['about_us_image']['tmp_name'];
            $fileName = $_FILES['about_us_image']['name'];
            $fileSize = $_FILES['about_us_image']['size'];
            $fileType = $_FILES['about_us_image']['type'];

            // Specify the directory where the image will be saved
            $uploadFileDir = './images/';
            $dest_path = $uploadFileDir . $fileName;

            // Move the file to the specified directory
            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                $image_path = $dest_path;
            }
        }

        // Update about us in the database
        $stmt = $pdo->prepare("UPDATE about_us SET title = :title, description = :description, image_path = :image_path WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':image_path', $image_path);
        $stmt->execute();
    }

    // Delete About Us
    if (isset($_GET['delete_about_us'])) {
        $id = $_GET['delete_about_us'];
        $stmt = $pdo->prepare("DELETE FROM about_us WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
    }
}

// Get current page
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

// Dashboard data
if ($page == 'dashboard') {
    // Product stock
    $stockData = $pdo->query("SELECT name, stock FROM products ORDER BY stock ASC LIMIT 5")
                      ->fetchAll(PDO::FETCH_ASSOC);

    // Sales data for chart
    $salesData = $pdo->query("SELECT DATE_FORMAT(sale_date, '%b') as month, SUM(amount) as amount
                              FROM sales
                              GROUP BY MONTH(sale_date)
                              ORDER BY MONTH(sale_date)
                              LIMIT 6")
                      ->fetchAll(PDO::FETCH_ASSOC);
}

// Products page data
if ($page == 'products') {
    // Products
    $products = $pdo->query("SELECT id, name, description, price, stock, images FROM products ORDER BY name")
                    ->fetchAll(PDO::FETCH_ASSOC);
}

// Edit product page data
if ($page == 'edit_product' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $product = $pdo->prepare("SELECT id, name, description, price, stock, images FROM products WHERE id = :id");
    $product->bindParam(':id', $id);
    $product->execute();
    $product = $product->fetch(PDO::FETCH_ASSOC);
}

// Ensure $banners is initialized at the start
$banners = [];

// Homepage Banners page data
if ($page == 'homepage_banners') {
    // Fetch banners from the database
    $banners = $pdo->query("SELECT id, title, description, image_path FROM banners ORDER BY title")
                    ->fetchAll(PDO::FETCH_ASSOC);

    // Debugging: Output the value of $banners
    error_log("Banners: " . print_r($banners, true));

    // If $banners is null, set it to an empty array
    if ($banners === false) {
        $banners = [];
    }
} 




// Edit Homepage Banner page data
if ($page == 'edit_banner' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $banner = $pdo->prepare("SELECT id, title, description, image_path FROM banners WHERE id = :id");
    $banner->bindParam(':id', $id);
    $banner->execute();
    $banner = $banner->fetch(PDO::FETCH_ASSOC);
}

// Menu Items page data
if ($page == 'menu_items') {
    // Menu Items
    $menu_items = $pdo->query("SELECT id, name, description, price, image_path, category FROM menu_items ORDER BY name")
                      ->fetchAll(PDO::FETCH_ASSOC);
}

// Edit Menu Item page data
if ($page == 'edit_menu_item' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $menu_item = $pdo->prepare("SELECT id, name, description, price, image_path, category FROM menu_items WHERE id = :id");
    $menu_item->bindParam(':id', $id);
    $menu_item->execute();
    $menu_item = $menu_item->fetch(PDO::FETCH_ASSOC);
}

// About Us page data
if ($page == 'about_us') {
    // About Us
    $about_us = $pdo->query("SELECT id, title, description, image_path FROM about_us ORDER BY title")
                    ->fetchAll(PDO::FETCH_ASSOC);
}

// Edit About Us page data
if ($page == 'edit_about_us' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $about_us = $pdo->prepare("SELECT id, title, description, image_path FROM about_us WHERE id = :id");
    $about_us->bindParam(':id', $id);
    $about_us->execute();
    $about_us = $about_us->fetch(PDO::FETCH_ASSOC);
}

// User Account page data
if ($page == 'user_account') {
    // User Account
    $user_account = $pdo->query("SELECT id, username, email, first_name, last_name, role FROM users WHERE id = 1")
                        ->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Starbucks Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --sb-green: #006341;
            --sb-light: #d4e9e2;
            --sb-gold: #cba258;
            --sb-dark: #1e3932;
            --white: #ffffff;
            --light-gray: #f9f9f9;
            --medium-gray: #e0e0e0;
            --dark-gray: #333333;
            --red: #d62b1f;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--light-gray);
            color: var(--dark-gray);
        }

        /* Header */
        header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background-color: var(--sb-dark);
            color: var(--white);
            padding: 10px 20px;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: bold;
        }

        nav {
            display: flex;
            gap: 20px;
        }

        nav a {
            color: var(--white);
            text-decoration: none;
            padding: 10px 15px;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        nav a:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        /* Main Container */
        .admin-container {
            display: flex;
            min-height: 100vh;
            margin-top: 60px; /* Space for fixed header */
        }

        /* Sidebar */
        .sidebar {
            width: 250px;
            background-color: var(--sb-dark);
            color: var(--white);
            padding: 20px 0;
            transition: all 0.3s;
        }

        .sidebar-nav ul {
            list-style: none;
        }

        .sidebar-nav li a {
            color: var(--white);
            text-decoration: none;
            padding: 12px 20px;
            display: flex;
            align-items: center;
            transition: all 0.3s;
        }

        .sidebar-nav li a:hover, .sidebar-nav li a.active {
            background-color: rgba(255,255,255,0.1);
            border-left: 4px solid var(--sb-gold);
        }

        /* Main Content */
        .main-content {
            flex: 1;
            padding: 20px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        /* Dashboard Grid */
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        /* Cards */
        .card {
            background: var(--white);
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            padding: 20px;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }

        .card h2 {
            margin-bottom: 20px;
            color: var(--sb-green);
            font-size: 1.2rem;
            font-weight: 500;
            border-bottom: 1px solid var(--sb-light);
            padding-bottom: 10px;
        }

        /* Tables */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        table th {
            background-color: var(--sb-light);
            color: var(--sb-green);
            font-weight: 500;
            text-align: left;
            padding: 12px 15px;
        }

        table td {
            padding: 12px 15px;
            border-bottom: 1px solid var(--medium-gray);
        }

        table tr:hover {
            background-color: rgba(0,99,65,0.05);
        }

        /* Buttons */
        .btn {
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
        }

        .btn-primary {
            background-color: var(--sb-green);
            color: var(--white);
        }

        .btn-primary:hover {
            background-color: var(--sb-dark);
        }

        .btn-danger {
            background-color: var(--red);
            color: var(--white);
        }

        .btn-danger:hover {
            background-color: #b8241a;
        }

        /* Forms */
        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--sb-dark);
        }

        .form-control {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid var(--medium-gray);
            border-radius: 4px;
            font-family: inherit;
            transition: border-color 0.3s;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--sb-green);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .admin-container {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="logo">Starbucks LOGO</div>
        <nav>
            <a href="?page=dashboard" class="<?= $page == 'dashboard' ? 'active' : '' ?>">Dashboard</a>
            <a href="?page=products" class="<?= $page == 'products' ? 'active' : '' ?>">Product</a>
            <a href="logout.php">Log out</a>
        </nav>
    </header>

    <div class="admin-container">
        <!-- Sidebar Navigation -->
        <aside class="sidebar">
            <nav class="sidebar-nav">
                <ul>
                    <li>
                        <a href="?page=dashboard" class="<?= $page == 'dashboard' ? 'active' : '' ?>">
                            <i class="fas fa-tachometer-alt"></i>
                            Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="?page=products" class="<?= $page == 'products' ? 'active' : '' ?>">
                            <i class="fas fa-coffee"></i>
                            Products
                        </a>
                    </li>
                    <li>
                        <a href="?page=homepage_banners" class="<?= $page == 'homepage_banners' ? 'active' : '' ?>">
                            <i class="fas fa-images"></i>
                            Homepage Banners
                        </a>
                    </li>
                    <li>
                        <a href="?page=menu_items" class="<?= $page == 'menu_items' ? 'active' : '' ?>">
                            <i class="fas fa-utensils"></i>
                            Menu Items
                        </a>
                    </li>
                    <li>
                        <a href="?page=about_us" class="<?= $page == 'about_us' ? 'active' : '' ?>">
                            <i class="fas fa-info-circle"></i>
                            About Us
                        </a>
                    </li>
                    <li>
                        <a href="?page=user_account" class="<?= $page == 'user_account' ? 'active' : '' ?>">
                            <i class="fas fa-user"></i>
                            User Account
                        </a>
                    </li>
                    <li>
                        <a href="logout.php">
                            <i class="fas fa-sign-out-alt"></i>
                            Log Out
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="header">
                <h1>
                    <?= $page == 'dashboard' ? 'Dashboard Overview' : ($page == 'products' ? 'Product Management' : ($page == 'homepage_banners' ? 'Homepage Banners' : ($page == 'menu_items' ? 'Menu Items' : ($page == 'about_us' ? 'About Us' : ($page == 'user_account' ? 'User Account' : 'Edit Product'))))) ?>
                </h1>
            </div>

            <?php if ($page == 'dashboard'): ?>
                <!-- Dashboard Content -->
                <div class="dashboard-grid">
                    <!-- User Logins Card -->
                    <div class="card">
                        <h2><i class="fas fa-users"></i> SALES</h2>
                            <table>
                                <thead>
                                    <tr>
                                        <th>Month</th>
                                        <th>Sales Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($salesData as $sale): ?>
                                        <tr>
                                        <td><?= htmlspecialchars($sale['month']) ?></td>
                                        <td>$<?= number_format($sale['amount'], 2) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                    </div>

                    <!-- Stock Status Card -->
                    <div class="card">
                        <h2><i class="fas fa-boxes"></i> Low Stock Products</h2>
                        <table>
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Stock</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($stockData as $product):
                                    $status = $product['stock'] < 10 ? 'Low' : ($product['stock'] < 30 ? 'Medium' : 'Good');
                                    $color = $product['stock'] < 10 ? 'var(--red)' : ($product['stock'] < 30 ? '#e67e22' : 'var(--sb-green)');
                                ?>
                                <tr>
                                    <td><?= htmlspecialchars($product['name']) ?></td>
                                    <td><?= $product['stock'] ?></td>
                                    <td style="color: <?= $color ?>"><?= $status ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Sales Chart Card -->
                    <div class="card">
                        <h2><i class="fas fa-chart-line"></i> Sales Statistics (Last 6 Months)</h2>
                        <div class="chart-container">
                            <canvas id="salesChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Recent Orders Card -->
                <div class="card">
                    <h2><i class="fas fa-receipt"></i> Recent Orders</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($orders) && is_array($orders)): ?>
                                    <?php foreach ($orders as $order): ?>
                                    <!-- Table rows here -->
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="5">No orders found.</td></tr>
                                <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            <?php elseif ($page == 'products'): ?>
                <!-- Products Content -->
                <div class="card">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <h2><i class="fas fa-coffee"></i> Product List</h2>
                        <button class="btn btn-primary" onclick="location.href='?page=add_product'">
                            <i class="fas fa-plus"></i> Add Product
                        </button>
                    </div>

                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Price</th>
                                <th>Image</th>
                                <th>Stock</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($products) > 0): ?>
                                <?php foreach ($products as $product): ?>
                                <tr>
                                    <td><?= $product['id'] ?></td>
                                    <td><?= htmlspecialchars($product['name']) ?></td>
                                    <td><?= htmlspecialchars(substr($product['description'], 0, 50)) ?>...</td>
                                    <td>$<?= number_format($product['price'], 2) ?></td>
                                    <td>
                                        <?php if (!empty($product['images'])): ?>
                                            <img src="<?= htmlspecialchars($product['images']) ?>" alt="Product Image" style="width: 50px; height: auto; border-radius: 5px;">
                                        <?php else: ?>
                                            <span>No image</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= $product['stock'] ?></td>
                                    <td>
                                        <button class="btn btn-primary btn-sm" onclick="location.href='?page=edit_product&id=<?= $product['id'] ?>'">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <button class="btn btn-danger btn-sm" onclick="confirmDelete(<?= $product['id'] ?>)">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" style="text-align: center;">No products found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            <?php elseif ($page == 'add_product' || $page == 'edit_product'): ?>
                <!-- Add/Edit Product Form -->
                <div class="card">
                    <h2><?= $page == 'add_product' ? '<i class="fas fa-plus"></i> Add Product' : '<i class="fas fa-edit"></i> Edit Product' ?></h2>
                    <form method="POST" enctype="multipart/form-data" class="form">
                        <input type="hidden" name="product_id" value="<?= isset($product['id']) ? $product['id'] : '' ?>">
                        <div class="form-group">
                            <label for="product_name">Product Name</label>
                            <input type="text" id="product_name" name="product_name" class="form-control" value="<?= isset($product['name']) ? htmlspecialchars($product['name']) : '' ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="product_description">Description</label>
                            <textarea id="product_description" name="product_description" class="form-control" required><?= isset($product['description']) ? htmlspecialchars($product['description']) : '' ?></textarea>
                        </div>
                        <div class="form-group">
                            <label for="product_price">Price</label>
                            <input type="number" id="product_price" name="product_price" class="form-control" step="0.01" value="<?= isset($product['price']) ? $product['price'] : '' ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="product_stock">Stock</label>
                            <input type="number" id="product_stock" name="product_stock" class="form-control" value="<?= isset($product['stock']) ? $product['stock'] : '' ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="product_image">Image</label>
                            <input type="file" id="product_image" name="product_image" class="form-control">
                            <?php if (isset($product['images']) && !empty($product['images'])): ?>
                                <img src="<?= htmlspecialchars($product['images']) ?>" alt="Product Image" style="width: 100px; height: auto; margin-top: 10px; border-radius: 5px;">
                            <?php endif; ?>
                        </div>
                        <button type="submit" name="<?= $page == 'add_product' ? 'add_product' : 'edit_product' ?>" class="btn btn-primary">
                            <?= $page == 'add_product' ? '<i class="fas fa-plus"></i> Add Product' : '<i class="fas fa-edit"></i> Update Product' ?>
                        </button>
                    </form>
                </div>
            <?php elseif ($page == 'homepage_banners'): ?>
                <!-- Homepage Banners Content -->
                <div class="card">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <h2><i class="fas fa-images"></i> Homepage Banners</h2>
                        <button class="btn btn-primary" onclick="location.href='?page=add_banner'">
                            <i class="fas fa-plus"></i> Add Banner
                        </button>
                    </div>

                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Title</th>
                                <th>Description</th>
                                <th>Image</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($bannerData) > 0): ?>
                                <?php foreach ($bannerData as $bdata): ?>
                                <tr>
                                    <td><?= $bdata['id'] ?></td>
                                    <td><?= htmlspecialchars($bdata['title']) ?></td>
                                    <td><?= htmlspecialchars(substr($bdata['description'], 0, 50)) ?>...</td>
                                    <td>
                                        <?php if (!empty($bdata['image_path'])): ?>
                                            <img src="<?= htmlspecialchars($bdata['image_path']) ?>" alt="Banner Image" style="width: 50px; height: auto; border-radius: 5px;">
                                        <?php else: ?>
                                            <span>No image</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-primary btn-sm" onclick="location.href='?page=edit_banner&id=<?= $bdata['id'] ?>'">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-danger btn-sm" onclick="confirmDeleteBanner(<?= $bdata['id'] ?>)">
                                            <i class="fas fa-trash"></i>    
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" style="text-align: center;">No banners found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            <?php elseif ($page == 'add_banner' || $page == 'edit_banner'): ?>
                <!-- Add/Edit Homepage Banner Form -->
                <div class="card">
                    <h2><?= $page == 'add_banner' ? '<i class="fas fa-plus"></i> Add Banner' : '<i class="fas fa-edit"></i> Edit Banner' ?></h2>
                    <form method="POST" enctype="multipart/form-data" class="form">
                        <input type="hidden" name="banner_id" value="<?= isset($bannerData['id']) ? $bannerData['id'] : '' ?>">
                        <div class="form-group">
                            <label for="banner_title">Banner Title</label>
                            <input type="text" id="banner_title" name="banner_title" class="form-control" value="<?= isset($bannerData['title']) ? htmlspecialchars($bannerData['title']) : '' ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="banner_description">Description</label>
                            <textarea id="banner_description" name="banner_description" class="form-control" required><?= isset($bannerData['description']) ? htmlspecialchars($bannerData['description']) : '' ?></textarea>
                        </div>
                        <div class="form-group">
                            <label for="banner_image">Image</label>
                            <input type="file" id="banner_image" name="banner_image" class="form-control">
                            <?php if (isset($bannerData['image_path']) && !empty($bannerData['image_path'])): ?>
                                <img src="<?= htmlspecialchars($bannerData['image_path']) ?>" alt="Banner Image" style="width: 100px; height: auto; margin-top: 10px; border-radius: 5px;">
                            <?php endif; ?>
                        </div>
                        <button type="submit" name="<?= $page == 'add_banner' ? 'add_banner' : 'edit_banner' ?>" class="btn btn-primary">
                            <?= $page == 'add_banner' ? '<i class="fas fa-plus"></i> Add Banner' : '<i class="fas fa-edit"></i> Update Banner' ?>
                        </button>
                    </form>
                </div>
            <?php elseif ($page == 'menu_items'): ?>
                <!-- Menu Items Content -->
                <div class="card">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <h2><i class="fas fa-utensils"></i> Menu Items</h2>
                        <button class="btn btn-primary" onclick="location.href='?page=add_menu_item'">
                            <i class="fas fa-plus"></i> Add Menu Item
                        </button>
                    </div>

                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Price</th>
                                <th>Image</th>
                                <th>Category</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($menu_items) > 0): ?>
                                <?php foreach ($menu_items as $menu_item): ?>
                                <tr>
                                    <td><?= $menu_item['id'] ?></td>
                                    <td><?= htmlspecialchars($menu_item['name']) ?></td>
                                    <td><?= htmlspecialchars(substr($menu_item['description'], 0, 50)) ?>...</td>
                                    <td>$<?= number_format($menu_item['price'], 2) ?></td>
                                    <td>
                                        <?php if (!empty($menu_item['image_path'])): ?>
                                            <img src="<?= htmlspecialchars($menu_item['image_path']) ?>" alt="Menu Item Image" style="width: 50px; height: auto; border-radius: 5px;">
                                        <?php else: ?>
                                            <span>No image</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($menu_item['category']) ?></td>
                                    <td>
                                        <button class="btn btn-primary btn-sm" onclick="location.href='?page=edit_menu_item&id=<?= $menu_item['id'] ?>'">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <button class="btn btn-danger btn-sm" onclick="confirmDeleteMenuItem(<?= $menu_item['id'] ?>)">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" style="text-align: center;">No menu items found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            <?php elseif ($page == 'add_menu_item' || $page == 'edit_menu_item'): ?>
                <!-- Add/Edit Menu Item Form -->
                <div class="card">
                    <h2><?= $page == 'add_menu_item' ? '<i class="fas fa-plus"></i> Add Menu Item' : '<i class="fas fa-edit"></i> Edit Menu Item' ?></h2>
                    <form method="POST" enctype="multipart/form-data" class="form">
                        <input type="hidden" name="menu_item_id" value="<?= isset($menu_item['id']) ? $menu_item['id'] : '' ?>">
                        <div class="form-group">
                            <label for="menu_item_name">Menu Item Name</label>
                            <input type="text" id="menu_item_name" name="menu_item_name" class="form-control" value="<?= isset($menu_item['name']) ? htmlspecialchars($menu_item['name']) : '' ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="menu_item_description">Description</label>
                            <textarea id="menu_item_description" name="menu_item_description" class="form-control" required><?= isset($menu_item['description']) ? htmlspecialchars($menu_item['description']) : '' ?></textarea>
                        </div>
                        <div class="form-group">
                            <label for="menu_item_price">Price</label>
                            <input type="number" id="menu_item_price" name="menu_item_price" class="form-control" step="0.01" value="<?= isset($menu_item['price']) ? $menu_item['price'] : '' ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="menu_item_category">Category</label>
                            <input type="text" id="menu_item_category" name="menu_item_category" class="form-control" value="<?= isset($menu_item['category']) ? htmlspecialchars($menu_item['category']) : '' ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="menu_item_image">Image</label>
                            <input type="file" id="menu_item_image" name="menu_item_image" class="form-control">
                            <?php if (isset($menu_item['image_path']) && !empty($menu_item['image_path'])): ?>
                                <img src="<?= htmlspecialchars($menu_item['image_path']) ?>" alt="Menu Item Image" style="width: 100px; height: auto; margin-top: 10px; border-radius: 5px;">
                            <?php endif; ?>
                        </div>
                        <button type="submit" name="<?= $page == 'add_menu_item' ? 'add_menu_item' : 'edit_menu_item' ?>" class="btn btn-primary">
                            <?= $page == 'add_menu_item' ? '<i class="fas fa-plus"></i> Add Menu Item' : '<i class="fas fa-edit"></i> Update Menu Item' ?>
                        </button>
                    </form>
                </div>
            <?php elseif ($page == 'about_us'): ?>
                <!-- About Us Content -->
                <div class="card">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <h2><i class="fas fa-info-circle"></i> About Us</h2>
                        <button class="btn btn-primary" onclick="location.href='?page=add_about_us'">
                            <i class="fas fa-plus"></i> Add About Us
                        </button>
                    </div>

                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Title</th>
                                <th>Description</th>
                                <th>Image</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($about_us) > 0): ?>
                                <?php foreach ($about_us as $about): ?>
                                <tr>
                                    <td><?= $about['id'] ?></td>
                                    <td><?= htmlspecialchars($about['title']) ?></td>
                                    <td><?= htmlspecialchars(substr($about['description'], 0, 50)) ?>...</td>
                                    <td>
                                        <?php if (!empty($about['image_path'])): ?>
                                            <img src="<?= htmlspecialchars($about['image_path']) ?>" alt="About Us Image" style="width: 50px; height: auto; border-radius: 5px;">
                                        <?php else: ?>
                                            <span>No image</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-primary btn-sm" onclick="location.href='?page=edit_about_us&id=<?= $about['id'] ?>'">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <button class="btn btn-danger btn-sm" onclick="confirmDeleteAboutUs(<?= $about['id'] ?>)">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" style="text-align: center;">No about us found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            <?php elseif ($page == 'add_about_us' || $page == 'edit_about_us'): ?>
                <!-- Add/Edit About Us Form -->
                <div class="card">
                    <h2><?= $page == 'add_about_us' ? '<i class="fas fa-plus"></i> Add About Us' : '<i class="fas fa-edit"></i> Edit About Us' ?></h2>
                    <form method="POST" enctype="multipart/form-data" class="form">
                        <input type="hidden" name="about_us_id" value="<?= isset($about_us['id']) ? $about_us['id'] : '' ?>">
                        <div class="form-group">
                            <label for="about_us_title">About Us Title</label>
                            <input type="text" id="about_us_title" name="about_us_title" class="form-control" value="<?= isset($about_us['title']) ? htmlspecialchars($about_us['title']) : '' ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="about_us_description">Description</label>
                            <textarea id="about_us_description" name="about_us_description" class="form-control" required><?= isset($about_us['description']) ? htmlspecialchars($about_us['description']) : '' ?></textarea>
                        </div>
                        <div class="form-group">
                            <label for="about_us_image">Image</label>
                            <input type="file" id="about_us_image" name="about_us_image" class="form-control">
                            <?php if (isset($about_us['image_path']) && !empty($about_us['image_path'])): ?>
                                <img src="<?= htmlspecialchars($about_us['image_path']) ?>" alt="About Us Image" style="width: 100px; height: auto; margin-top: 10px; border-radius: 5px;">
                            <?php endif; ?>
                        </div>
                        <button type="submit" name="<?= $page == 'add_about_us' ? 'add_about_us' : 'edit_about_us' ?>" class="btn btn-primary">
                            <?= $page == 'add_about_us' ? '<i class="fas fa-plus"></i> Add About Us' : '<i class="fas fa-edit"></i> Update About Us' ?>
                        </button>
                    </form>
                </div>
            <?php elseif ($page == 'user_account'): ?>
                <!-- User Account Content -->
                <div class="card">
                    <h2><i class="fas fa-user"></i> User Account</h2>
                    <form method="POST" class="form">
                        <input type="hidden" name="user_account_id" value="<?= isset($user_account['id']) ? $user_account['id'] : '' ?>">
                        <div class="form-group">
                            <label for="user_account_username">Username</label>
                            <input type="text" id="user_account_username" name="user_account_username" class="form-control" value="<?= isset($user_account['username']) ? htmlspecialchars($user_account['username']) : '' ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="user_account_email">Email</label>
                            <input type="email" id="user_account_email" name="user_account_email" class="form-control" value="<?= isset($user_account['email']) ? htmlspecialchars($user_account['email']) : '' ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="user_account_first_name">First Name</label>
                            <input type="text" id="user_account_first_name" name="user_account_first_name" class="form-control" value="<?= isset($user_account['first_name']) ? htmlspecialchars($user_account['first_name']) : '' ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="user_account_last_name">Last Name</label>
                            <input type="text" id="user_account_last_name" name="user_account_last_name" class="form-control" value="<?= isset($user_account['last_name']) ? htmlspecialchars($user_account['last_name']) : '' ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="user_account_role">Role</label>
                            <input type="text" id="user_account_role" name="user_account_role" class="form-control" value="<?= isset($user_account['role']) ? htmlspecialchars($user_account['role']) : '' ?>" required>
                        </div>
                        <button type="submit" name="update_user_account" class="btn btn-primary">
                            <i class="fas fa-edit"></i> Update User Account
                        </button>
                    </form>
                </div>
            <?php endif; ?>
        </main>
    </div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        <?php if ($page == 'dashboard' && !empty($salesData)): ?>
        // Sales Chart
        const salesCtx = document.getElementById('salesChart').getContext('2d');
        const salesChart = new Chart(salesCtx, {
            type: 'line',
            data: {
                labels: <?= json_encode(array_column($salesData, 'month')) ?>,
                datasets: [{
                    label: 'Monthly Sales ($)',
                    data: <?= json_encode(array_column($salesData, 'amount')) ?>,
                    backgroundColor: 'rgba(0, 99, 65, 0.1)',
                    borderColor: 'rgba(0, 99, 65, 1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: 'rgba(0, 99, 65, 1)',
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            padding: 20
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return '$' + context.raw.toLocaleString();
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '$' + value.toLocaleString();
                            }
                        },
                        grid: {
                            drawBorder: false,
                            color: 'rgba(0,0,0,0.05)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
        <?php endif; ?>
    });

    function confirmDelete(productId) {
        if (confirm('Are you sure you want to delete this product?')) {
            window.location.href = '?page=products&delete_product=' + productId;
        }
    }

    function confirmDeleteBanner(bannerId) {
        if (confirm('Are you sure you want to delete this banner?')) {
            window.location.href = '?page=homepage_banners&delete_banner=' + bannerId;
        }
    }

    function confirmDeleteMenuItem(menuItemId) {
        if (confirm('Are you sure you want to delete this menu item?')) {
            window.location.href = '?page=menu_items&delete_menu_item=' + menuItemId;
        }
    }

    function confirmDeleteAboutUs(aboutUsId) {
        if (confirm('Are you sure you want to delete this about us?')) {
            window.location.href = '?page=about_us&delete_about_us=' + aboutUsId;
        }
    }
</script>
</body>
</html>
