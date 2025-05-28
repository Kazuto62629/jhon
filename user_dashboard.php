<?php
session_start();
require 'database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: index.php');
    exit;
}

$userId = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Initialize or load cart from session
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}
$cart = &$_SESSION['cart'];

// Handle adding items to cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $productId = (int)$_POST['product_id'];
    $quantity = max(1, (int)($_POST['quantity'] ?? 1));

    $stmt = $pdo->prepare("SELECT id, name, price FROM products WHERE id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();

    if ($product) {
        $found = false;
        foreach ($cart as &$item) {
            if ($item['product_id'] === $productId) {
                $item['quantity'] += $quantity;
                $found = true;
                break;
            }
        }
        if (!$found) {
            $cart[] = ['product_id' => $productId, 'name' => $product['name'], 'price' => $product['price'], 'quantity' => $quantity];
        }
    }

    header("Location: user_dashboard.php");
    exit;
}

// Handle updating cart quantities or deleting cart items
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cart'])) {
    $updates = $_POST['quantities'] ?? [];
    $deleteIds = $_POST['delete'] ?? [];

    foreach ($cart as $idx => $item) {
        $pid = $item['product_id'];
        if (in_array($pid, $deleteIds)) {
            unset($cart[$idx]);
            continue;
        }
        if (isset($updates[$pid])) {
            $newQty = (int)$updates[$pid];
            if ($newQty < 1) {
                unset($cart[$idx]);
            } else {
                $cart[$idx]['quantity'] = $newQty;
            }
        }
    }
    $_SESSION['cart'] = array_values($cart);

    header("Location: user_dashboard.php");
    exit;
}

// Handle placing order
$order_placed_msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    if (empty($cart)) {
        $order_placed_msg = "Your cart is empty. Please add items before placing an order.";
    } else {
        $pickup_or_delivery = ($_POST['pickup_or_delivery'] === 'delivery') ? 'delivery' : 'pickup';

        $total = 0;
        foreach ($cart as $item) {
            $total += $item['price'] * $item['quantity'];
        }

        $stmt = $pdo->prepare("INSERT INTO orders (user_id, status, pickup_or_delivery, total_amount) VALUES (?, 'pending', ?, ?)");
        $stmt->execute([$userId, $pickup_or_delivery, $total]);
        $orderId = $pdo->lastInsertId();

        $stmtItem = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity) VALUES (?, ?, ?)");
        foreach ($cart as $item) {
            $stmtItem->execute([$orderId, $item['product_id'], $item['quantity']]);
        }

        $_SESSION['cart'] = [];
        $order_placed_msg = "Order placed successfully! Your order ID is #" . $orderId . ".";
        $cart = [];
    }
}

$stmt = $pdo->query("SELECT id, name, description, price FROM products ORDER BY name ASC");
$products = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$userId]);
$orders = $stmt->fetchAll();

$totalCartItems = array_sum(array_column($cart, 'quantity'));

?>

<?php
session_start();
// Redirect to login if not logged in or if role is not user
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: index.php');
    exit();
}

// Initialize cart count based on session cart
$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
$cart_count = 0;
foreach ($cart as $item) {
    $cart_count += $item['quantity'];
}

// Example categories and products
$categories = [
    1 => 'Coffee',
    2 => 'Tea',
    3 => 'Bakery',
    4 => 'Cold Drinks',
];

$products = [
    ['id'=>1, 'name'=>'Caffè Latte', 'price'=>4.50, 'image'=>'https://content-prod-live.cert.starbucks.com/binary/v2/asset/137-65962.jpg', 'category_id'=>1],
    ['id'=>2, 'name'=>'Cappuccino', 'price'=>4.00, 'image'=>'https://content-prod-live.cert.starbucks.com/binary/v2/asset/137-65959.jpg', 'category_id'=>1],
    ['id'=>3, 'name'=>'Caramel Macchiato', 'price'=>5.00, 'image'=>'https://content-prod-live.cert.starbucks.com/binary/v2/asset/137-65954.jpg', 'category_id'=>1],
    ['id'=>4, 'name'=>'Pumpkin Spice Latte', 'price'=>5.50, 'image'=>'https://content-prod-live.cert.starbucks.com/binary/v2/asset/137-66579.jpg', 'category_id'=>1],
    ['id'=>5, 'name'=>'Green Tea', 'price'=>3.50, 'image'=>'https://upload.wikimedia.org/wikipedia/commons/thumb/f/f9/Green_tea_leaf.jpg/800px-Green_tea_leaf.jpg', 'category_id'=>2],
    ['id'=>6, 'name'=>'Chai Tea', 'price'=>3.75, 'image'=>'https://upload.wikimedia.org/wikipedia/commons/thumb/4/43/Chai_tea.jpg/640px-Chai_tea.jpg', 'category_id'=>2],
    ['id'=>7, 'name'=>'Blueberry Muffin', 'price'=>2.50, 'image'=>'https://content-prod-live.cert.starbucks.com/binary/v2/asset/137-68663.jpg', 'category_id'=>3],
    ['id'=>8, 'name'=>'Chocolate Chip Cookie', 'price'=>2.00, 'image'=>'https://content-prod-live.cert.starbucks.com/binary/v2/asset/137-68676.jpg', 'category_id'=>3],
    ['id'=>9, 'name'=>'Iced Coffee', 'price'=>3.75, 'image'=>'https://content-prod-live.cert.starbucks.com/binary/v2/asset/137-67178.jpg', 'category_id'=>4],
    ['id'=>10, 'name'=>'Lemonade', 'price'=>3.25, 'image'=>'https://content-prod-live.cert.starbucks.com/binary/v2/asset/137-68882.jpg', 'category_id'=>4],
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Starbucks Coffee - User Dashboard</title>
    <style>
        :root {
            --starbucks-green: #00704a;
            --starbucks-dark-green: #004225;
            --starbucks-light-green: #c1d9c3;
            --starbucks-beige: #f6f6f1;
            --font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        * {
            box-sizing: border-box;
        }
        body, html {
            margin: 0; padding: 0;
            height: 100%;
            font-family: var(--font-family);
            background: var(--starbucks-beige);
            color: var(--starbucks-dark-green);
        }
        .container {
            display: flex;
            flex-direction: column;
            height: 100vh;
            width: 100vw;
        }
        header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: var(--starbucks-green);
            padding: 0 2rem;
            height: 80px;
            color: white;
            box-shadow: 0 4px 6px rgba(0,0,0,0.2);
            user-select: none;
        }
        .logo {
            display: flex;
            align-items: center;
            cursor: pointer;
        }
        .logo img {
            height: 60px;
            width: auto;
        }
        nav {
            flex: 1;
            display: flex;
            justify-content: center;
        }
        nav ul {
            list-style: none;
            display: flex;
            gap: 3rem;
            margin: 0;
            padding: 0;
            font-weight: 600;
            font-size: 1.1rem;
        }
        nav ul li {
            cursor: pointer;
            position: relative;
            padding-bottom: 5px;
            transition: color 0.3s ease;
        }
        nav ul li:hover,
        nav ul li.active {
            color: var(--starbucks-light-green);
        }
        nav ul li::after {
            content: '';
            position: absolute;
            width: 0%;
            height: 2px;
            background: var(--starbucks-light-green);
            bottom: 0;
            left: 0;
            transition: width 0.3s ease;
        }
        nav ul li:hover::after,
        nav ul li.active::after {
            width: 100%;
        }
        .cart-icon {
            cursor: pointer;
            font-size: 1.8rem;
            position: relative;
        }
        .cart-icon svg {
            fill: white;
            width: 32px;
            height: 32px;
            transition: transform 0.3s ease;
        }
        .cart-icon:hover svg {
            transform: scale(1.1);
        }
        main {
            flex: 1;
            padding: 3rem 2rem;
            background: #e9f2e8;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 2rem;
            text-align: center;
        }
        h1 {
            font-size: 2.5rem;
            margin: 0;
        }
        p {
            font-size: 1.2rem;
            max-width: 600px;
            margin: 0;
        }
        .nav-buttons {
            display: flex;
            gap: 2rem;
            margin-top: 2rem;
        }
        .nav-buttons a {
            background: var(--starbucks-green);
            color: white;
            padding: 1rem 2rem;
            border-radius: 30px;
            font-weight: 600;
            font-size: 1.1rem;
            text-decoration: none;
            box-shadow: 0 4px 6px rgba(0,0,0,0.2);
            transition: background-color 0.3s ease;
        }
        .nav-buttons a:hover {
            background: var(--starbucks-dark-green);
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <div class="logo" onclick="location.href='user_home.php'">
                <img src="https://upload.wikimedia.org/wikipedia/sr/thumb/4/4e/Starbucks_Corporation_Logo_2011.svg/1200px-Starbucks_Corporation_Logo_2011.svg.png" alt="Starbucks Logo" />
            </div>
            <nav>
                <ul>
                    <li class="active">Home</li>
                    <li onclick="location.href='products.php'" style="cursor:pointer;">Products</li>
                    <li onclick="location.href='cart.php'" style="cursor:pointer;">Cart</li>
                </ul>
            </nav>
            <div class="cart-icon" onclick="location.href='cart.php'" title="View Cart">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" >
                    <path d="M7 4h-2l-1 2h2l3.6 7.59-1.35 2.44c-.16.28-.25.61-.25.97 0 1.1.9 2 2 2h12v-2h-11.42c-.14 0-.25-.11-.25-.25l.03-.12.9-1.63h7.72c.75 0 1.41-.41 1.75-1.03l3.58-6.49-1.74-1-3.58 6.49h-7.72l-1.1-2z"/>
                    <circle cx="10.5" cy="19.5" r="1.5"/>
                    <circle cx="17.5" cy="19.5" r="1.5"/>
                </svg>  
            </div>
        </header>
        <main>
            <h1>Welcome back, <?php echo $username; ?>!</h1>
            <p>Your Starbucks Coffee Shop user dashboard. Explore our products or view your shopping cart below.</p>
            <div class="nav-buttons">
                <a href="products.php">Browse Products</a>
                <a href="cart.php">View Cart</a>
                <a href="logout.php">Logout</a>
            </div>
        </main>
    </div>
</body>
</html>

