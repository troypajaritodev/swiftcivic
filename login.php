<?php
require_once 'auth.php';
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        header('Location: login.php?error=empty');
        exit();
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['full_name'] = $user['full_name'];
            
            logAction($user['id'], 'login', 'User logged in');
            
            if ($user['role'] === 'admin') {
                header('Location: admin/index.php');
            } else {
                header('Location: citizen_dashboard.php');
            }
            exit();
        } else {
            header('Location: login.php?error=invalid');
            exit();
        }
    }
}

$pageTitle = 'Login';
require_once 'header.php';
?>

<div class="max-w-md mx-auto mt-8 md:mt-16 px-4">
    <div class="bg-white rounded-civic shadow-lg p-6 md:p-8">
        <h1 class="text-2xl md:text-3xl font-bold text-navy mb-6 text-center">Login</h1>
        
        <?php if (isset($_GET['error'])): ?>
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-civic mb-4">
                <?php if ($_GET['error'] === 'invalid'): ?>
                    Invalid email or password.
                <?php elseif ($_GET['error'] === 'empty'): ?>
                    Please enter both email and password.
                <?php else: ?>
                    An error occurred. Please try again.
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                <input type="email" id="email" name="email" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-civic focus:ring-2 focus:ring-skyblue focus:border-transparent"
                       value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
            </div>
            
            <div class="mb-6">
                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                <input type="password" id="password" name="password" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-civic focus:ring-2 focus:ring-skyblue focus:border-transparent">
            </div>
            
            <button type="submit"
                    class="w-full bg-skyblue hover:bg-blue-600 text-white font-semibold py-3 px-4 rounded-civic transition duration-200">
                Login
            </button>
        </form>
        
        <p class="mt-4 text-center text-sm text-gray-600">
            Don't have an account?
            <a href="register.php" class="text-skyblue hover:underline font-medium">Register here</a>
        </p>
    </div>
</div>

<?php require_once 'footer.php'; ?>
