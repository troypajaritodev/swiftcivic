<?php
require_once 'auth.php';
redirectIfNotLoggedIn();

$request_id = isset($_GET['request_id']) ? (int)$_GET['request_id'] : 0;

if ($request_id <= 0) {
    header('Location: citizen_dashboard.php?error=invalid_request');
    exit();
}

// Verify the request belongs to the logged-in user and is cancelable
$stmt = $pdo->prepare("SELECT * FROM requests WHERE id = ? AND user_id = ?");
$stmt->execute([$request_id, $_SESSION['user_id']]);
$request = $stmt->fetch();

if (!$request) {
    header('Location: citizen_dashboard.php?error=not_found');
    exit();
}

// Only allow cancellation for Awaiting Payment or Verification Pending
if (!in_array($request['status'], ['Awaiting Payment', 'Verification Pending'])) {
    header('Location: citizen_dashboard.php?error=cannot_cancel');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $confirm = $_POST['confirm'] ?? '';
    
    if ($confirm !== 'yes') {
        $error = 'Please confirm cancellation by checking the box.';
    } else {
        try {
            $pdo->beginTransaction();
            
            // Delete related payment record if exists
            $stmt = $pdo->prepare("DELETE FROM payments WHERE request_id = ?");
            $stmt->execute([$request_id]);
            
            // Delete the request
            $stmt = $pdo->prepare("DELETE FROM requests WHERE id = ?");
            $stmt->execute([$request_id]);
            
            // Log the action
            logAction($_SESSION['user_id'], 'cancel_request', "Cancelled request #$request_id (Tracking: {$request['tracking_number']})");
            
            $pdo->commit();
            
            header('Location: citizen_dashboard.php?success=cancelled');
            exit();
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = 'Failed to cancel request. Please try again.';
            error_log("Cancel request error: " . $e->getMessage());
        }
    }
}

$pageTitle = 'Cancel Request';
require_once 'header.php';
?>

<div class="max-w-md mx-auto mt-8 md:mt-16 px-4">
    <div class="bg-white rounded-civic shadow-lg p-6 md:p-8">
        <h1 class="text-2xl md:text-3xl font-bold text-navy mb-4">Cancel Request</h1>
        
        <div class="bg-yellow-50 border border-yellow-200 rounded-civic p-4 mb-6">
            <p class="text-yellow-800 font-medium">Warning: This action cannot be undone!</p>
            <p class="text-yellow-700 text-sm mt-2">
                You are about to cancel request <strong><?= htmlspecialchars($request['tracking_number']) ?></strong>
                (<?= htmlspecialchars($request['doc_type']) ?>).
            </p>
        </div>
        
        <?php if ($error): ?>
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-civic mb-4">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="mb-6">
                <label class="flex items-center">
                    <input type="checkbox" name="confirm" value="yes" class="mr-2">
                    <span class="text-sm text-gray-700">I understand this action cannot be undone</span>
                </label>
            </div>
            
            <div class="flex gap-3">
                <a href="citizen_dashboard.php" 
                   class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-3 px-4 rounded-civic text-center transition duration-200">
                    Go Back
                </a>
                <button type="submit"
                        class="flex-1 bg-red-600 hover:bg-red-700 text-white font-semibold py-3 px-4 rounded-civic transition duration-200">
                    Cancel Request
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once 'footer.php'; ?>
