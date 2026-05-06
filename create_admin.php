<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'db.php';

echo "=== SwiftCivic Admin User Fix ===\n\n";

// Check if admin@swiftcivic.com exists
$stmt = $pdo->prepare("SELECT id, email, role FROM users WHERE email = ?");
$stmt->execute(['admin@swiftcivic.com']);
$user = $stmt->fetch();

if ($user) {
    echo "✅ admin@swiftcivic.com already exists\n";
    echo "   ID: " . $user['id'] . "\n";
    echo "   Role: " . $user['role'] . "\n";
    
    // Update password
    $newPassword = 'EvilfNrfQ0W';
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("UPDATE users SET password = ?, role = 'admin' WHERE id = ?");
    if ($stmt->execute([$hashedPassword, $user['id']])) {
        echo "✅ Password updated for admin@swiftcivic.com\n";
        echo "   New password: $newPassword\n";
    } else {
        echo "❌ Failed to update password\n";
    }
} else {
    echo "❌ admin@swiftcivic.com NOT found, creating...\n";
    
    $email = 'admin@swiftcivic.com';
    $password = 'EvilfNrfQ0W';
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("INSERT INTO users (email, password, full_name, role) VALUES (?, ?, 'Admin User', 'admin')");
    if ($stmt->execute([$email, $hashedPassword])) {
        echo "✅ Admin user created!\n";
        echo "   Email: $email\n";
        echo "   Password: $password\n";
        echo "   ID: " . $pdo->lastInsertId() . "\n";
    } else {
        echo "❌ Failed to create admin user\n";
    }
}

echo "\n=== Test Login ===\n";
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute(['admin@swiftcivic.com']);
$user = $stmt->fetch();

if ($user && password_verify('EvilfNrfQ0W', $user['password'])) {
    echo "✅ Login test PASSED!\n";
    echo "   User ID: " . $user['id'] . "\n";
    echo "   Role: " . $user['role'] . "\n";
    echo "   Should redirect to: admin/index.php ✅\n";
} else {
    echo "❌ Login test FAILED!\n";
}

echo "\n=== All Admin Users ===\n";
$stmt = $pdo->query("SELECT id, email, role FROM users WHERE role = 'admin'");
$admins = $stmt->fetchAll();
foreach ($admins as $admin) {
    echo "   ID: " . $admin['id'] . ", Email: " . $admin['email'] . ", Role: " . $admin['role'] . "\n";
}
