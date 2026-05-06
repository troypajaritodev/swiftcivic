<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>SwiftCivic Login Test</h1>";

// Test 1: PHP Version
echo "<h2>1. PHP Version</h2>";
echo "PHP Version: " . phpversion() . "<br>";

// Test 2: Database Connection
echo "<h2>2. Database Connection</h2>";
try {
    require_once 'db.php';
    echo "✅ Database connected successfully<br>";
    
    // Test 3: Check admin user
    echo "<h2>3. Admin User Check</h2>";
    $stmt = $pdo->prepare("SELECT id, email, role FROM users WHERE email = ?");
    $stmt->execute(['admin@swiftcivic.com']);
    $admin = $stmt->fetch();
    
    if ($admin) {
        echo "✅ Admin user found<br>";
        echo "Email: " . $admin['email'] . "<br>";
        echo "Role: " . $admin['role'] . "<br>";
        echo "ID: " . $admin['id'] . "<br>";
        
        // Test 4: Verify password
        echo "<h2>4. Password Verification</h2>";
        if (password_verify('EvilfNrfQ0W', $admin['password'])) {
            echo "✅ Password is correct!<br>";
            echo "Login should work!<br>";
        } else {
            echo "❌ Password is WRONG!<br>";
        }
    } else {
        echo "❌ Admin user NOT found<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "<br>";
}

// Test 5: Session
echo "<h2>5. Session Test</h2>";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
echo "Session Status: " . session_status() . "<br>";
echo "Session ID: " . session_id() . "<br>";

// Test URLs
echo "<h2>6. Test Login</h2>";
echo "Login page: <a href='login.php'>http://localhost:8000/login.php</a><br>";
echo "Admin login: admin@swiftcivic.com / EvilfNrfQ0W<br>";
echo "Should redirect to: <a href='admin/index.php'>admin/index.php</a> ✅<br><br>";

echo "<strong>Open browser and visit: http://localhost:8000/login.php</strong>";
?>
