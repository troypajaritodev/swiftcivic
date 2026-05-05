<?php
// Database configuration - Auto-configured for Render
// Render sets DATABASE_URL environment variable automatically

if (getenv('DATABASE_URL')) {
    // Running on Render
    $db_url = parse_url(getenv('DATABASE_URL'));
    define('DB_HOST', $db_url['host']);
    define('DB_NAME', ltrim($db_url['path'], '/'));
    define('DB_USER', $db_url['user']);
    define('DB_PASS', $db_url['pass']);
} elseif (getenv('DB_HOST')) {
    // Manual environment variables
    define('DB_HOST', getenv('DB_HOST'));
    define('DB_NAME', getenv('DB_NAME'));
    define('DB_USER', getenv('DB_USER'));
    define('DB_PASS', getenv('DB_PASS'));
} else {
    // Local/XAMPP/InfinityFree fallback
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'swiftcivic');
    define('DB_USER', 'root');
    define('DB_PASS', '');
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
