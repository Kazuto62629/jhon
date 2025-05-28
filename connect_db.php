<?php
require 'database.php';

try {
    // Seed initial products
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM products");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $products = [
            ['Espresso', 'Strong black espresso shot', 3.00],
            ['Cappuccino', 'Espresso with steamed milk and foam', 4.50],
            ['Latte', 'Creamy milk with a shot of espresso', 4.75],
            ['Mocha', 'Chocolate and espresso combined', 5.25],
            ['Americano', 'Espresso diluted with hot water', 3.25],
            ['Caramel Macchiato', 'Espresso with milk & caramel syrup', 5.00]
        ];

        $insertProduct = $pdo->prepare("INSERT INTO products (name, description, price) VALUES (?, ?, ?)");
        foreach ($products as $p) {
            $insertProduct->execute([$p[0], $p[1], $p[2]]);
        }
    }

    // Seed admin user if not exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE role='admin'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $adminUsername = 'admin';
        $adminPassword = password_hash('adminpass', PASSWORD_DEFAULT);
        $insertUser = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'admin')");
        $insertUser->execute([$adminUsername, $adminPassword]);
    }

    echo "Database initialized and seeded successfully.";

} catch (PDOException $e) {
    echo "Error initializing database: " . $e->getMessage();
}
?>
