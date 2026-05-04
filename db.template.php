<?php
// Database configuration - Update these values for your environment
// For InfinityFree: Use your InfinityFree database credentials
// For Localhost: Use your XAMPP/MySQL credentials

if (!file_exists(__DIR__ . '/db.local.php')) {
    // Production/InfinityFree settings
    define('DB_HOST', 'your_host_here');
    define('DB_NAME', 'your_database_name');
    define('DB_USER', 'your_username');
    define('DB_PASS', 'your_password');
} else {
    // Local development settings (create db.local.php with your local credentials)
    require_once 'db.local.php';
}

// Start session for auth
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    error_log("DB Connection Error: " . $e->getMessage());
    die("System temporarily unavailable. Please try again later.");
}
