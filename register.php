<?php
require_once 'auth.php';

if (isLoggedIn()) {
    header('Location: citizen_dashboard.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $full_name = filter_input(INPUT_POST, 'full_name', FILTER_DEFAULT);

    if (empty($email) || empty($password) || empty($full_name)) {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        if (registerUser($email, $password, $full_name)) {
            $user_id = $pdo->lastInsertId();
            logAction($user_id, 'User registered', '', null);
            header('Location: register_success.php');
            exit();
        } else {
            $error = 'Email already registered.';
        }
    }
}

$pageTitle = 'Register';
require_once 'header.php';
?>

<div class="min-h-screen bg-light-gray flex items-center justify-center py-12">
    <div class="w-full max-w-md">
        <div class="bg-white rounded-civic shadow-lg p-8">
            <div class="mb-8">
                <h2 class="text-2xl font-bold text-navy mb-2">Create an Account</h2>
                <div class="w-12 h-1 bg-skyblue"></div>
            </div>
            
            <?php if ($error): ?>
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-civic mb-6">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" class="space-y-6">
                <div>
                    <label for="full_name" class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                    <input type="text" id="full_name" name="full_name" required
                           class="w-full px-4 py-3 border border-gray-300 rounded-civic focus:ring-2 focus:ring-skyblue focus:border-transparent"
                           value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>">
                </div>
                
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                    <input type="email" id="email" name="email" required
                           class="w-full px-4 py-3 border border-gray-300 rounded-civic focus:ring-2 focus:ring-skyblue focus:border-transparent"
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>
                
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                    <input type="password" id="password" name="password" required
                           class="w-full px-4 py-3 border border-gray-300 rounded-civic focus:ring-2 focus:ring-skyblue focus:border-transparent">
                </div>
                
                <div>
                    <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-2">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required
                           class="w-full px-4 py-3 border border-gray-300 rounded-civic focus:ring-2 focus:ring-skyblue focus:border-transparent">
                </div>
                
                <button type="submit" class="w-full bg-navy hover:bg-blue-900 text-white font-semibold py-3 px-4 rounded-civic transition duration-200">
                    Create Account
                </button>
            </form>
            
            <div class="mt-6 text-center">
                <p class="text-gray-600">
                    Already have an account?
                    <a href="login.php" class="text-skyblue hover:underline font-medium">Login here</a>
                </p>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
