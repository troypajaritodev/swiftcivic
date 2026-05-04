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
        exit;
    }
}

function redirectIfNotStaff() {
    if (!isStaff()) {
        header('Location: index.php');
        exit;
    }
}

function getCurrentUser() {
    if (!isLoggedIn()) return null;
    
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function logAction($user_id, $action, $details = '') {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO logs (user_id, action, details, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->execute([$user_id, $action, $details]);
}

function generateTrackingCode() {
    return 'TRK' . strtoupper(bin2hex(random_bytes(4))) . time();
}
