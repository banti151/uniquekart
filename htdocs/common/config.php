<?php
// Start session in a central place
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- DATABASE CONFIGURATION ---
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'uniquekartkart_db');
define('DB_USER', 'root');
define('DB_PASS', 'root');

// --- SITE CONFIGURATION ---
define('SITE_NAME', 'uniquekartKart');
define('BASE_URL', 'http://' . $_SERVER['HTTP_HOST'] . '/uniquekartKart'); // Adjust folder name if needed

// --- DATABASE CONNECTION ---
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("ERROR: Could not connect. " . $e->getMessage());
}

// --- HELPER FUNCTIONS ---
function check_login() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit();
    }
}

function check_admin_login() {
    if (!isset($_SESSION['admin_id'])) {
        header('Location: login.php');
        exit();
    }
}
?>