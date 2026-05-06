<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Simulate a POST request to test login
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST['email'] = 'admin@swiftcivic.com';
$_POST['password'] = 'EvilfNrfQ0W';

require_once 'db.php';
require_once 'auth.php';

echo "=== Testing Login Logic ===\n";

// Check if user exists
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$_POST['email']]);
$user = $stmt->fetch();

if ($user) {
    echo "✅ User found: " . $user['email'] . "\n";
    echo "Role: " . $user['role'] . "\n";
    
    if (password_verify($_POST['password'], $user['password'])) {
        echo "✅ Password correct!\n";
        echo "✅ Login would succeed!\n";
        
        if ($user['role'] === 'admin') {
            echo "✅ Would redirect to: admin/index.php\n";
        } else {
            echo "Would redirect to: citizen_dashboard.php\n";
        }
    } else {
        echo "❌ Password incorrect!\n";
    }
} else {
    echo "❌ User not found!\n";
    echo "Available users:\n";
    $stmt = $pdo->query("SELECT id, email, role FROM users");
    $users = $stmt->fetchAll();
    foreach ($users as $u) {
        echo "  - ID: " . $u['id'] . ", Email: " . $u['email'] . ", Role: " . $u['role'] . "\n";
    }
}
