<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../auth.php';

// Check if admin already exists
$stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE role IN ('admin', 'staff')");
$adminCount = $stmt->fetch()['count'];

if ($adminCount > 0 && !isAdmin()) {
    die("Setup already completed or access denied.");
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $full_name = $_POST['full_name'] ?? '';
    $full_name = filter_var($full_name, FILTER_DEFAULT);

    if (empty($email) || empty($password) || empty($full_name)) {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("INSERT INTO users (email, password, full_name, role) VALUES (?, ?, ?, 'admin')");
        if ($stmt->execute([$email, $hashedPassword, $full_name])) {
            $success = 'Admin account created successfully! You can now <a href="verify.php" class="text-skyblue underline">login here</a>.';
        } else {
            $error = 'Failed to create admin account. Email may already exist.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        navy: '#0B1F3A',
                        skyblue: '#0EA5E9'
                    },
                    borderRadius: {
                        'civic': '14px'
                    }
                }
            }
        }
    </script>
    <title>SwiftCivic - Admin Setup</title>
</head>
<body class="bg-light-gray min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full bg-white rounded-civic shadow-lg p-8">
        <h1 class="text-3xl font-bold text-navy mb-6 text-center">SwiftCivic Setup</h1>
        <p class="text-gray-600 mb-6 text-center">Create the first admin account</p>

        <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-civic mb-4">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-civic mb-4">
                <?= $success ?>
            </div>
        <?php else: ?>
            <form method="POST" action="" class="space-y-6">
                <div>
                    <label for="full_name" class="block text-sm font-medium text-gray-700 mb-2">Admin Name *</label>
                    <input type="text" id="full_name" name="full_name" required
                           class="w-full px-4 py-3 border border-gray-300 rounded-civic focus:ring-2 focus:ring-skyblue focus:border-transparent">
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Admin Email *</label>
                    <input type="email" id="email" name="email" required
                           class="w-full px-4 py-3 border border-gray-300 rounded-civic focus:ring-2 focus:ring-skyblue focus:border-transparent">
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password *</label>
                    <input type="password" id="password" name="password" required minlength="6"
                           class="w-full px-4 py-3 border border-gray-300 rounded-civic focus:ring-2 focus:ring-skyblue focus:border-transparent">
                </div>

                <div>
                    <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-2">Confirm Password *</label>
                    <input type="password" id="confirm_password" name="confirm_password" required minlength="6"
                           class="w-full px-4 py-3 border border-gray-300 rounded-civic focus:ring-2 focus:ring-skyblue focus:border-transparent">
                </div>

                <button type="submit" class="w-full bg-skyblue hover:bg-blue-600 text-white font-bold py-3 px-4 rounded-civic transition duration-200">
                    Create Admin Account
                </button>
            </form>
        <?php endif; ?>

        <p class="mt-4 text-center text-sm text-gray-500">
            <a href="../index.php" class="text-skyblue hover:underline">Back to Home</a>
        </p>
    </div>
</body>
</html>
