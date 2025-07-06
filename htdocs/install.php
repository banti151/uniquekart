<?php
// --- CONFIGURATION ---
$db_host = '127.0.0.1';
$db_user = 'root';
$db_pass = 'root';
$db_name = 'uniquekartkart_db';
$admin_user = 'admin';
$admin_pass = 'admin123';

// --- INSTALLATION LOGIC ---
header('Content-Type: text/plain');
echo "uniquekartKart Installer\n";
echo "========================\n\n";

// Step 1: Create required directories
echo "Step 1: Creating directories...\n";
$dirs = ['uploads', 'uploads/categories', 'uploads/products'];
foreach ($dirs as $dir) {
    if (!is_dir($dir)) {
        if (mkdir($dir, 0777, true)) {
            echo "  - Created directory: $dir\n";
        } else {
            die("  - FAILED to create directory: $dir. Please check permissions.\n");
        }
    } else {
        echo "  - Directory already exists: $dir\n";
    }
}
echo "Step 1: Complete.\n\n";

// Step 2: Connect to MySQL and create database
echo "Step 2: Setting up database...\n";
try {
    $pdo = new PDO("mysql:host=$db_host", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "  - Database '$db_name' created or already exists.\n";
    
    // Connect to the specific database
    $pdo->exec("USE `$db_name`");
    echo "  - Connected to database '$db_name'.\n";

} catch (PDOException $e) {
    die("  - FAILED to connect or create database: " . $e->getMessage() . "\n");
}
echo "Step 2: Complete.\n\n";


// Step 3: Create tables
echo "Step 3: Creating tables...\n";
try {
    // Users Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `users` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(100) NOT NULL,
        `phone` VARCHAR(15) NOT NULL UNIQUE,
        `email` VARCHAR(100) NOT NULL UNIQUE,
        `password` VARCHAR(255) NOT NULL,
        `address` TEXT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB;");
    echo "  - Table 'users' created.\n";

    // Admin Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `admin` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `username` VARCHAR(50) NOT NULL UNIQUE,
        `password` VARCHAR(255) NOT NULL
    ) ENGINE=InnoDB;");
    echo "  - Table 'admin' created.\n";

    // Categories Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `categories` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(100) NOT NULL,
        `image` VARCHAR(255) NOT NULL
    ) ENGINE=InnoDB;");
    echo "  - Table 'categories' created.\n";

    // Products Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `products` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `cat_id` INT NOT NULL,
        `name` VARCHAR(255) NOT NULL,
        `description` TEXT NOT NULL,
        `price` DECIMAL(10, 2) NOT NULL,
        `stock` INT NOT NULL DEFAULT 0,
        `image` VARCHAR(255) NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`cat_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB;");
    echo "  - Table 'products' created.\n";

    // Orders Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `orders` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `user_id` INT NOT NULL,
        `shipping_address` TEXT NOT NULL,
        `total_amount` DECIMAL(10, 2) NOT NULL,
        `status` ENUM('Placed', 'Dispatched', 'Delivered', 'Cancelled') NOT NULL DEFAULT 'Placed',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB;");
    echo "  - Table 'orders' created.\n";

    // Order Items Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `order_items` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `order_id` INT NOT NULL,
        `product_id` INT NOT NULL,
        `quantity` INT NOT NULL,
        `price` DECIMAL(10, 2) NOT NULL,
        FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB;");
    echo "  - Table 'order_items' created.\n";

} catch (PDOException $e) {
    die("  - FAILED to create tables: " . $e->getMessage() . "\n");
}
echo "Step 3: Complete.\n\n";

// Step 4: Insert default admin user
echo "Step 4: Inserting default admin user...\n";
try {
    // Check if admin already exists
    $stmt = $pdo->prepare("SELECT id FROM admin WHERE username = ?");
    $stmt->execute([$admin_user]);
    if ($stmt->fetch()) {
        echo "  - Admin user '$admin_user' already exists. Skipping.\n";
    } else {
        $hashed_password = password_hash($admin_pass, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO admin (username, password) VALUES (?, ?)");
        $stmt->execute([$admin_user, $hashed_password]);
        echo "  - Admin user created.\n";
        echo "    Username: $admin_user\n";
        echo "    Password: $admin_pass\n";
    }
} catch(PDOException $e) {
    die("  - FAILED to insert admin user: " . $e->getMessage() . "\n");
}
echo "Step 4: Complete.\n\n";


// Step 5: Insert Sample Data
echo "Step 5: Inserting sample data...\n";
try {
    // Sample Categories
    $pdo->exec("INSERT INTO `categories` (`id`, `name`, `image`) VALUES
        (1, 'Electronics', 'uploads/categories/electronics.png'),
        (2, 'Fashion', 'uploads/categories/fashion.png'),
        (3, 'Home', 'uploads/categories/home.png'),
        (4, 'Mobiles', 'uploads/categories/mobiles.png')
        ON DUPLICATE KEY UPDATE name=VALUES(name);");
    echo "  - Sample categories inserted.\n";

    // Sample Products
    $pdo->exec("INSERT INTO `products` (`id`, `cat_id`, `name`, `description`, `price`, `stock`, `image`) VALUES
        (1, 4, 'Smartphone Pro X', 'The latest and greatest smartphone with an amazing camera and long-lasting battery.', 69999.00, 50, 'https://placehold.co/600x600/3B82F6/FFFFFF/png?text=Phone'),
        (2, 1, 'Wireless Earbuds', 'Crystal clear sound with noise cancellation. Perfect for music and calls.', 7999.00, 150, 'https://placehold.co/600x600/10B981/FFFFFF/png?text=Earbuds'),
        (3, 2, 'Classic Denim Jacket', 'A timeless denim jacket that never goes out of style. Made with premium cotton.', 2499.00, 80, 'https://placehold.co/600x600/F59E0B/FFFFFF/png?text=Jacket'),
        (4, 3, 'Smart Coffee Maker', 'Brew the perfect cup of coffee from your phone. Supports voice commands.', 4999.00, 30, 'https://placehold.co/600x600/6366F1/FFFFFF/png?text=Coffee'),
        (5, 1, '4K Smart TV 55-inch', 'Experience lifelike picture quality with this stunning 4K Smart TV.', 45000.00, 25, 'https://placehold.co/600x600/EF4444/FFFFFF/png?text=TV')
        ON DUPLICATE KEY UPDATE name=VALUES(name);");
    echo "  - Sample products inserted.\n";

} catch (PDOException $e) {
    echo "  - WARNING: Could not insert sample data. " . $e->getMessage() . "\n";
}
echo "Step 5: Complete.\n\n";

echo "INSTALLATION SUCCESSFUL!\n";
echo "You will be redirected to the login page in 5 seconds...";

// Redirect after 5 seconds
header("refresh:5;url=login.php");
exit();
?>