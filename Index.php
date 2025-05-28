<?php
session_start();
require 'includes/database.php';

// Redirect logged in users
if (isset($_SESSION['user_id'])) {
    header('Location: ' . ($_SESSION['role'] === 'admin' ? 'admin_dashboard.php' : 'user_dashboard.php'));
    exit;
}

// Initialize variables
$errors = [];
$register_success = false;

// Handle registration
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $username = trim($_POST['reg_username'] ?? '');
    $password = $_POST['reg_password'] ?? '';
    $confirm_password = $_POST['reg_password_confirm'] ?? '';
    $role = $_POST['reg_role'] ?? 'user'; // Default to user if not specified

    // Validation
    if (empty($username) || empty($password) || empty($confirm_password)) {
        $errors['register'] = "All fields are required.";
    } elseif ($password !== $confirm_password) {
        $errors['register'] = "Passwords don't match.";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $errors['register'] = "Username already taken.";
        }
    }

    if (empty($errors)) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
        $stmt->execute([$username, $password_hash, $role]);
        $register_success = true;
    }
}

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = trim($_POST['login_username'] ?? '');
    $password = $_POST['login_password'] ?? '';

    if (empty($username) || empty($password)) {
        $errors['login'] = "Username and password required.";
    } else {
        $stmt = $pdo->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            header('Location: ' . ($user['role'] === 'admin' ? 'admin_dashboard.php' : 'user_dashboard.php'));
            exit;
        } else {
            $errors['login'] = "Invalid credentials.";
        }
    }
}

// Get active banners for the homepage
$banners = $pdo->query("SELECT * FROM banners WHERE is_active = TRUE ORDER BY display_order LIMIT 3")->fetchAll();

// Get featured menu items
$featured_items = $pdo->query("SELECT * FROM menu_items WHERE is_featured = TRUE AND is_active = TRUE LIMIT 4")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Starbucks Philippines | Coffee Company</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --starbucks-green: #006341;
            --starbucks-light: #d4e9e2;
            --starbucks-gold: #cba258;
        }
        
        body {
            font-family: 'SoDoSans', 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #000000;
        }
        
        .navbar {
            background-color: white;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .navbar-brand img {
            height: 50px;
        }
        
        .nav-link {
            color: #000000 !important;
            font-weight: 600;
            padding: 8px 16px;
        }
        
        .btn-success {
            background-color: var(--starbucks-green);
            border-color: var(--starbucks-green);
        }
        
        .btn-gold {
            background-color: var(--starbucks-gold);
            border-color: var(--starbucks-gold);
            color: #000;
        }
        
        .hero-banner {
            background-color: var(--starbucks-light);
            padding: 60px 0;
        }
        
        .feature-card {
            border: none;
            border-radius: 10px;
            overflow: hidden;
            transition: transform 0.3s;
        }
        
        .feature-card:hover {
            transform: translateY(-5px);
        }
        
        .feature-card img {
            height: 200px;
            object-fit: cover;
        }
        
        .footer {
            background-color: var(--starbucks-green);
            color: white;
            padding: 40px 0;
        }
        
        .auth-modal .modal-content {
            border-radius: 10px;
        }
        
        .auth-tabs .nav-link {
            color: #495057 !important;
        }
        
        .auth-tabs .nav-link.active {
            color: var(--starbucks-green) !important;
            font-weight: bold;
            border-bottom: 3px solid var(--starbucks-green);
        }
        
        .role-selection {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
        }
        
        .role-btn {
            padding: 10px 20px;
            margin: 0 10px;
            border: 2px solid transparent;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .role-btn.active {
            border-color: var(--starbucks-green);
            background-color: var(--starbucks-light);
        }
        
        .role-btn:hover {
            background-color: var(--starbucks-light);
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light sticky-top">
        <div class="container">
            <a class="navbar-brand" href="#">
                <img src="https://logos-world.net/wp-content/uploads/2020/09/Starbucks-Logo.png" alt="Starbucks Logo">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#">MENU</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">REWARDS</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">GIFT CARDS</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?action=debug">SHOP</a>
                    </li>
                </ul>
                <div class="d-flex">
                    <a href="#" class="btn btn-outline-dark me-2" data-bs-toggle="modal" data-bs-target="#loginModal">
                        Sign in
                    </a>
                    <div class="dropdown">
                        <button class="btn btn-success dropdown-toggle" type="button" id="registerDropdown" data-bs-toggle="dropdown">
                            Join now
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#registerUserModal">Join as Customer</a></li>
                            <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#registerAdminModal">Join as Admin</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Banner -->
    <div class="hero-banner">
        <div class="container">
            <div id="bannerCarousel" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-inner">
                    <?php foreach ($banners as $index => $banner): ?>
                    <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                        <img src="<?= htmlspecialchars($banner['image_path']) ?>" class="d-block w-100" alt="<?= htmlspecialchars($banner['title']) ?>">
                    </div>
                    <?php endforeach; ?>
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#bannerCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#bannerCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Featured Products -->
    <section class="py-5">
        <div class="container">
            <h2 class="text-center mb-5">Featured Products</h2>
            <div class="featured-items">
            <?php foreach ($featured_items as $item): ?>
                <div class="featured-item">
                    <img src="<?= htmlspecialchars($item['image_path']) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                    <h3><?= htmlspecialchars($item['name']) ?></h3>
                    <p><?= htmlspecialchars($item['description']) ?></p>
                    <p>₱<?= number_format($item['price'], 2) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h2>About Starbucks Philippines</h2>
                    <p>Starbucks Philippines is committed to serving the finest coffee and creating meaningful connections in every cup.</p>
                    <a href="about.php" class="btn btn-outline-success">Learn More</a>
                </div>
                <div class="col-md-6">
                    <img src="https://media.glamour.com/photos/64e4c40d7a845edcc2cf9fe4/master/w_2560%2Cc_limit/GL_8.22_Pumpkin-Spice-Lattejpg.jpg" class="img-fluid rounded" alt="About Starbucks">
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-3">
                    <h5>About Us</h5>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-white">Our Company</a></li>
                        <li><a href="#" class="text-white">Our Coffee</a></li>
                        <li><a href="#" class="text-white">Stories and News</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h5>Careers</h5>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-white">Culture and Values</a></li>
                        <li><a href="#" class="text-white">Opportunities</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h5>Social Impact</h5>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-white">Community</a></li>
                        <li><a href="#" class="text-white">Ethical Sourcing</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h5>Order and Pickup</h5>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-white">Order on the App</a></li>
                        <li><a href="#" class="text-white">Delivery Options</a></li>
                    </ul>
                </div>
            </div>
            <hr class="bg-light">
            <div class="text-center">
                <p>&copy; 2023 Starbucks Coffee Company. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Login Modal -->
    <div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="loginModalLabel">Sign In to Starbucks</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="">
                        <input type="hidden" name="login" value="1">
                        <div class="mb-3">
                            <label for="login_username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="login_username" name="login_username" required>
                        </div>
                        <div class="mb-3">
                            <label for="login_password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="login_password" name="login_password" required>
                        </div>
                        <?php if (!empty($errors['login'])): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($errors['login']) ?></div>
                        <?php endif; ?>
                        <button type="submit" class="btn btn-success w-100">Sign In</button>
                        <div class="text-center mt-3">
                            <a href="#" data-bs-toggle="modal" data-bs-target="#registerUserModal" data-bs-dismiss="modal">Create an account</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Register as User Modal -->
    <div class="modal fade" id="registerUserModal" tabindex="-1" aria-labelledby="registerUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="registerUserModalLabel">Join as Customer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="">
                        <input type="hidden" name="register" value="1">
                        <input type="hidden" name="reg_role" value="user">
                        <div class="mb-3">
                            <label for="reg_username_user" class="form-label">Username</label>
                            <input type="text" class="form-control" id="reg_username_user" name="reg_username" required>
                        </div>
                        <div class="mb-3">
                            <label for="reg_password_user" class="form-label">Password</label>
                            <input type="password" class="form-control" id="reg_password_user" name="reg_password" required>
                        </div>
                        <div class="mb-3">
                            <label for="reg_password_confirm_user" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" id="reg_password_confirm_user" name="reg_password_confirm" required>
                        </div>
                        <?php if (!empty($errors['register'])): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($errors['register']) ?></div>
                        <?php elseif ($register_success && $_POST['reg_role'] === 'user'): ?>
                            <div class="alert alert-success">Registration successful! Please sign in.</div>
                        <?php endif; ?>
                        <button type="submit" class="btn btn-success w-100">Join as Customer</button>
                        <div class="text-center mt-3">
                            <a href="#" data-bs-toggle="modal" data-bs-target="#loginModal" data-bs-dismiss="modal">Already have an account? Sign in</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Register as Admin Modal -->
    <div class="modal fade" id="registerAdminModal" tabindex="-1" aria-labelledby="registerAdminModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="registerAdminModalLabel">Join as Admin</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Admin registration is for authorized personnel only.
                    </div>
                    <form method="POST" action="">
                        <input type="hidden" name="register" value="1">
                        <input type="hidden" name="reg_role" value="admin">
                        <div class="mb-3">
                            <label for="reg_username_admin" class="form-label">Username</label>
                            <input type="text" class="form-control" id="reg_username_admin" name="reg_username" required>
                        </div>
                        <div class="mb-3">
                            <label for="reg_password_admin" class="form-label">Password</label>
                            <input type="password" class="form-control" id="reg_password_admin" name="reg_password" required>
                        </div>
                        <div class="mb-3">
                            <label for="reg_password_confirm_admin" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" id="reg_password_confirm_admin" name="reg_password_confirm" required>
                        </div>
                        <div class="mb-3">
                            <label for="admin_code" class="form-label">Admin Access Code</label>
                            <input type="password" class="form-control" id="admin_code" name="admin_code" required>
                            <small class="text-muted">Contact system administrator for the access code</small>
                        </div>
                        <?php if (!empty($errors['register'])): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($errors['register']) ?></div>
                        <?php elseif ($register_success && $_POST['reg_role'] === 'admin'): ?>
                            <div class="alert alert-success">Admin account created successfully! Please sign in.</div>
                        <?php endif; ?>
                        <button type="submit" class="btn btn-gold w-100">Join as Admin</button>
                        <div class="text-center mt-3">
                            <a href="#" data-bs-toggle="modal" data-bs-target="#loginModal" data-bs-dismiss="modal">Already have an account? Sign in</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Automatically show modal if there are errors
        <?php if (!empty($errors)): ?>
            document.addEventListener('DOMContentLoaded', function() {
                <?php if (isset($_POST['login'])): ?>
                    var loginModal = new bootstrap.Modal(document.getElementById('loginModal'));
                    loginModal.show();
                <?php elseif (isset($_POST['register'])): ?>
                    <?php if ($_POST['reg_role'] === 'user'): ?>
                        var registerModal = new bootstrap.Modal(document.getElementById('registerUserModal'));
                        registerModal.show();
                    <?php else: ?>
                        var registerModal = new bootstrap.Modal(document.getElementById('registerAdminModal'));
                        registerModal.show();
                    <?php endif; ?>
                <?php endif; ?>
            });
        <?php endif; ?>
        
        // Handle dropdown to modal transition
        document.querySelectorAll('[data-bs-target="#registerUserModal"]').forEach(el => {
            el.addEventListener('click', function() {
                var dropdown = bootstrap.Dropdown.getInstance(document.querySelector('#registerDropdown'));
                if (dropdown) dropdown.hide();
            });
        });
        
        document.querySelectorAll('[data-bs-target="#registerAdminModal"]').forEach(el => {
            el.addEventListener('click', function() {
                var dropdown = bootstrap.Dropdown.getInstance(document.querySelector('#registerDropdown'));
                if (dropdown) dropdown.hide();
            });
        });
    </script>
</body>
</html>