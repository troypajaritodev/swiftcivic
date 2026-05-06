<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'db.php';

echo "=== SwiftCivic Admin Password Fix ===\n\n";

// Check admin users
$stmt = $pdo->query("SELECT id, email, password, role FROM users WHERE role = 'admin'");
$admins = $stmt->fetchAll();

if (count($admins) == 0) {
    echo "❌ No admin users found!\n";
    echo "Creating admin user...\n";
    
    $email = 'admin@swiftcivic.com';
    $password = 'EvilfNrfQ0W';
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("INSERT INTO users (email, password, full_name, role) VALUES (?, ?, 'Admin User', 'admin')");
    if ($stmt->execute([$email, $hashedPassword])) {
        echo "✅ Admin user created!\n";
        echo "Email: $email\n";
        echo "Password: $password\n";
    } else {
        echo "❌ Failed to create admin user\n";
    }
} else {
    echo "Admin users found:\n";
    foreach ($admins as $admin) {
        echo "\nID: " . $admin['id'] . "\n";
        echo "Email: " . $admin['email'] . "\n";
        echo "Role: " . $admin['role'] . "\n";
        
        // Update password for admin@swiftcivic.com or first admin
        if ($admin['email'] == 'admin@swiftcivic.com' || $admin['email'] == 'admin@swiftcivic.gov') {
            $newPassword = 'EvilfNrfQ0W';
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            if ($stmt->execute([$hashedPassword, $admin['id']])) {
                echo "✅ Password updated for " . $admin['email'] . "\n";
                echo "New password: $newPassword\n";
            } else {
                echo "❌ Failed to update password\n";
            }
        }
    }
}

echo "\n=== Test Login ===\n";
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute(['admin@swiftcivic.com']);
$user = $stmt->fetch();

if (!$user) {
    $stmt->execute(['admin@swiftcivic.gov']);
    $user = $stmt->fetch();
}

if ($user) {
    echo "✅ User found: " . $user['email'] . "\n";
    echo "Role: " . $user['role'] . "\n";
    
    if (password_verify('EvilfNrfQ0W', $user['password'])) {
        echo "✅ Password verification: SUCCESS!\n";
        echo "\nLogin credentials:\n";
        echo "Email: " . $user['email'] . "\n";
        echo "Password: EvilfNrfQ0W\n";
    } else {
        echo "❌ Password verification: FAILED!\n";
    }
} else {
    echo "❌ No admin user found!\n";
}
