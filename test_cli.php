<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== SwiftCivic Localhost Test ===\n\n";

// Test 1: PHP Version
echo "1. PHP Version: " . phpversion() . "\n";

// Test 2: Database Connection
echo "\n2. Database Connection:\n";
try {
    require_once 'db.php';
    echo "   ✅ Database connected successfully\n";
    
    // Test 3: Check tables
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "   Tables in database: " . count($tables) . "\n";
    foreach ($tables as $table) {
        echo "   - $table\n";
    }
    
    // Test 4: Check all users
    echo "\n3. All Users:\n";
    $stmt = $pdo->query("SELECT id, email, role FROM users");
    $users = $stmt->fetchAll();
    
    if (count($users) == 0) {
        echo "   ❌ NO USERS FOUND!\n";
        echo "   Creating admin user...\n";
        
        $email = 'admin@swiftcivic.com';
        $password = 'EvilfNrfQ0W';
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("INSERT INTO users (email, password, full_name, role) VALUES (?, ?, 'Admin User', 'admin')");
        if ($stmt->execute([$email, $hashedPassword])) {
            echo "   ✅ Admin user created!\n";
            echo "   Email: $email\n";
            echo "   Password: $password\n";
        } else {
            echo "   ❌ Failed to create admin user\n";
        }
    } else {
        foreach ($users as $user) {
            echo "   ID: " . $user['id'] . ", Email: " . $user['email'] . ", Role: " . $user['role'] . "\n";
        }
    }
    
    // Test 5: Verify admin password
    echo "\n4. Admin Password Verification:\n";
    $stmt = $pdo->prepare("SELECT id, email, password, role FROM users WHERE email = ?");
    $stmt->execute(['admin@swiftcivic.com']);
    $admin = $stmt->fetch();
    
    if ($admin) {
        echo "   ✅ Admin user found: " . $admin['email'] . "\n";
        echo "   Role: " . $admin['role'] . "\n";
        
        if (password_verify('EvilfNrfQ0W', $admin['password'])) {
            echo "   ✅ Password is correct!\n";
            echo "   Login should work!\n";
        } else {
            echo "   ❌ Password is WRONG!\n";
        }
    } else {
        echo "   ❌ Admin user NOT found\n";
    }
    
} catch (Exception $e) {
    echo "   ❌ Database error: " . $e->getMessage() . "\n";
}

// Test 6: Session
echo "\n5. Session Test:\n";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
echo "   Session Status: " . session_status() . "\n";
echo "   Session ID: " . session_id() . "\n";

echo "\n=== Test Complete ===\n";
echo "Open browser and visit: http://localhost/troywebapp/login.php\n";
echo "Login with: admin@swiftcivic.com / EvilfNrfQ0W\n";
echo "Should redirect to: http://localhost/troywebapp/admin/index.php\n";
