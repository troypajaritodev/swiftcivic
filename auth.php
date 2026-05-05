<?php
require_once 'db.php';

// Helper function to get correct file path for both localhost and InfinityFree
function getFullPath($relativePath) {
    $relativePath = ltrim($relativePath, '/');
    
    if (isset($_SERVER['DOCUMENT_ROOT']) && $_SERVER['DOCUMENT_ROOT'] !== '') {
        $fullPath = $_SERVER['DOCUMENT_ROOT'] . '/' . $relativePath;
        if (file_exists($fullPath)) {
            return $fullPath;
        }
    }
    
    return __DIR__ . '/' . $relativePath;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isStaff() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function redirectIfNotLoggedIn() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

function redirectIfNotStaff() {
    if (!isStaff()) {
        header('Location: index.php');
        exit();
    }
}

function getCurrentUser() {
    if (!isLoggedIn()) return null;
    
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function logAction($user_id, $action, $details = '', $request_id = null) {
    global $pdo;
    $ip = $_SERVER['REMOTE_ADDR'] ?? null;
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;
    $stmt = $pdo->prepare("INSERT INTO logs (user_id, request_id, action, user_agent, ip_address, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->execute([$user_id, $request_id, $action, $details, $ip]);
}

function registerUser($email, $password, $full_name) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        return false; // Email already exists
    }
    
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (email, password, full_name, role) VALUES (?, ?, ?, 'citizen')");
    return $stmt->execute([$email, $hashedPassword, $full_name]);
}

function generateTrackingCode() {
    return 'TRK' . strtoupper(bin2hex(random_bytes(4))) . time();
}
