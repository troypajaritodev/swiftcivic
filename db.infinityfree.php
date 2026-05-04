<?php
// Database configuration for InfinityFree
// Host: sql309.infinityfree.com
// Database: if0_41810514_swiftcivic
// Username: if0_41810514

define('DB_HOST', 'sql309.infinityfree.com');
define('DB_NAME', 'if0_41810514_swiftcivic');
define('DB_USER', 'if0_41810514');
define('DB_PASS', 'YOUR_PASSWORD_HERE'); // Replace with actual password

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
