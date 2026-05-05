<?php
require_once 'auth.php';
require_once 'db.php';

if (isLoggedIn()) {
    if (isStaff()) {
        header('Location: admin/index.php');
    } else {
        header('Location: citizen_dashboard.php');
    }
    exit();
}

redirectIfNotLoggedIn();

$user = getCurrentUser();
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = $_POST['full_name'] ?? '';
    $full_name = filter_var($full_name, FILTER_DEFAULT);
    $contact = $_POST['contact'] ?? '';
    $contact = filter_var($contact, FILTER_DEFAULT);
    $address = $_POST['address'] ?? '';
    $address = filter_var($address, FILTER_DEFAULT);

    if (empty($full_name)) {
        $error = 'Full name is required.';
    } else {
        $stmt = $pdo->prepare("UPDATE users SET full_name = ?, contact = ?, address = ?, updated_at = NOW() WHERE id = ?");
        if ($stmt->execute([$full_name, $contact, $address, $user['id']])) {
            $success = 'Profile updated successfully!';
            $user = getCurrentUser();
            $_SESSION['user_name'] = $user['full_name'];
        } else {
            $error = 'Failed to update profile.';
        }
    }
}

$pageTitle = 'My Profile';
require_once 'header.php';
?>

<div class="max-w-2xl mx-auto">
    <div class="mb-8">
        <h1 class="text-2xl md:text-3xl font-bold text-navy mb-2">My Profile</h1>
        <div class="w-12 h-1 bg-skyblue"></div>
    </div>

    <?php if ($success): ?>
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-civic mb-6">
            <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-civic mb-6">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <div class="bg-white rounded-civic shadow-lg p-8">
        <form method="POST" action="" class="space-y-6">
            <div>
                <label for="full_name" class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                <input type="text" id="full_name" name="full_name" required
                       class="w-full px-4 py-3 border border-gray-300 rounded-civic focus:ring-2 focus:ring-skyblue focus:border-transparent"
                       value="<?= htmlspecialchars($_POST['full_name'] ?? $user['full_name'] ?? '') ?>">
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                <input type="email" id="email" disabled
                       class="w-full px-4 py-3 border border-gray-300 rounded-civic bg-gray-100"
                       value="<?= htmlspecialchars($user['email'] ?? '') ?>">
                <p class="text-xs text-gray-500 mt-1">Email cannot be changed</p>
            </div>

            <div>
                <label for="contact" class="block text-sm font-medium text-gray-700 mb-2">Contact Number</label>
                <input type="text" id="contact" name="contact"
                       class="w-full px-4 py-3 border border-gray-300 rounded-civic focus:ring-2 focus:ring-skyblue focus:border-transparent"
                       value="<?= htmlspecialchars($_POST['contact'] ?? $user['contact'] ?? '') ?>">
            </div>

            <div>
                <label for="address" class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                <textarea id="address" name="address" rows="3"
                          class="w-full px-4 py-3 border border-gray-300 rounded-civic focus:ring-2 focus:ring-skyblue focus:border-transparent"><?= htmlspecialchars($_POST['address'] ?? $user['address'] ?? '') ?></textarea>
            </div>

            <button type="submit" class="w-full bg-skyblue hover:bg-blue-600 text-white font-semibold py-3 px-4 rounded-civic transition duration-200">
                Update Profile
            </button>
        </form>
    </div>
</div>

<?php require_once 'footer.php'; ?>
