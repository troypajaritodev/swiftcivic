<?php
require_once 'auth.php';
redirectIfNotLoggedIn();

$request_id = isset($_GET['request_id']) ? (int)$_GET['request_id'] : 0;

if ($request_id <= 0) {
    header('Location: citizen_dashboard.php');
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM requests WHERE id = ? AND user_id = ?");
$stmt->execute([$request_id, $_SESSION['user_id']]);
$request = $stmt->fetch();

if (!$request) {
    die("Request not found or access denied.");
}

// Only allow payment upload for Awaiting Payment or Action Required status
if (!in_array($request['status'], ['Awaiting Payment', 'Action Required'])) {
    header('Location: citizen_dashboard.php?error=invalid_status');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_method = filter_input(INPUT_POST, 'payment_method', FILTER_DEFAULT);
    $amount = 150.00; // Default amount

    if (!isset($_FILES['receipt']) || $_FILES['receipt']['error'] !== UPLOAD_ERR_OK) {
        $error = 'Please upload your payment receipt.';
    } elseif (empty($payment_method)) {
        $error = 'Please select a payment method.';
    } else {
        $file = $_FILES['receipt'];
        $allowedTypes = ['application/pdf', 'image/jpeg', 'image/png', 'image/jpg'];
        $maxSize = 10 * 1024 * 1024;

        if (!in_array($file['type'], $allowedTypes)) {
            $error = 'Invalid file type. Only PDF, JPG, PNG allowed.';
        } elseif ($file['size'] > $maxSize) {
            $error = 'File too large. Maximum 10MB allowed.';
        } else {
            $uploadDir = getFullPath('uploads/receipts/');
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = uniqid('receipt_', true) . '.' . $ext;
            $filepath = $uploadDir . $filename;

            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                $pdo->beginTransaction();
                try {
                    // Insert or update payment
                    $stmt = $pdo->prepare("
                        INSERT INTO payments (request_id, amount, payment_method, receipt_path, status, created_at)
                        VALUES (?, ?, ?, ?, 'Pending', NOW())
                        ON DUPLICATE KEY UPDATE 
                            amount = VALUES(amount), 
                            payment_method = VALUES(payment_method), 
                            receipt_path = VALUES(receipt_path), 
                            status = 'Pending', 
                            verified_at = NULL
                    ");
                    $stmt->execute([$request_id, $amount, $payment_method, 'uploads/receipts/' . $filename]);

                    // Update request status
                    $newStatus = 'Verification Pending';
                    $stmt = $pdo->prepare("UPDATE requests SET status = ? WHERE id = ?");
                    $stmt->execute([$newStatus, $request_id]);

                    // Log action
                    logAction('Payment uploaded', $request_id);

                    $pdo->commit();

                    // Send email notification
                    if (function_exists('sendStatusEmail')) {
                        sendStatusEmail($request_id, $newStatus);
                    }

                    header('Location: citizen_dashboard.php?payment_success=1');
                    exit();

                } catch (Exception $e) {
                    $pdo->rollBack();
                    $error = 'Failed to process payment: ' . $e->getMessage();
                }
            } else {
                $error = 'Failed to upload receipt.';
            }
        }
    }
}

$pageTitle = 'Upload Payment';
require_once 'header.php';
?>

<div class="max-w-2xl mx-auto">
    <div class="mb-8">
        <h1 class="text-2xl md:text-3xl font-bold text-navy mb-2">Upload Payment Receipt</h1>
        <div class="w-12 h-1 bg-skyblue"></div>
    </div>

    <div class="bg-white rounded-civic shadow-lg p-8">
        <!-- Request Info -->
        <div class="bg-light-gray p-4 rounded-civic mb-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <p class="text-sm text-gray-600">Tracking Number</p>
                    <p class="font-bold text-navy"><?= htmlspecialchars($request['tracking_number']) ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Document Type</p>
                    <p class="font-bold text-navy"><?= htmlspecialchars($request['doc_type']) ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Amount</p>
                    <p class="font-bold text-navy">₱150.00</p>
                </div>
            </div>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-civic mb-6">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="" enctype="multipart/form-data" class="space-y-6">
            <div>
                <label for="payment_method" class="block text-sm font-medium text-gray-700 mb-2">Payment Method</label>
                <select id="payment_method" name="payment_method" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-civic focus:ring-2 focus:ring-skyblue focus:border-transparent"
                        onchange="toggleQR(this.value)">
                    <option value="">Select payment method...</option>
                    <option value="GCash" <?= (isset($_POST['payment_method']) && $_POST['payment_method'] === 'GCash' ? 'selected' : '') ?>>GCash</option>
                    <option value="Bank Transfer" <?= (isset($_POST['payment_method']) && $_POST['payment_method'] === 'Bank Transfer' ? 'selected' : '') ?>>Bank Transfer</option>
                    <option value="Over the Counter" <?= (isset($_POST['payment_method']) && $_POST['payment_method'] === 'Over the Counter' ? 'selected' : '') ?>>Over the Counter</option>
                </select>
            </div>

             <!-- GCash QR Code (shown when GCash is selected) -->
            <div id="gcash_qr" class="hidden mb-6 text-center bg-light-gray p-6 rounded-civic">
                <h3 class="text-lg font-bold text-navy mb-4">Scan to Pay via GCash</h3>
                <?php if (file_exists(__DIR__ . '/assets/images/gcash_qr.png')): ?>
                    <div class="bg-white p-4 rounded-civic inline-block mb-4">
                        <img src="assets/images/gcash_qr.png" alt="GCash QR Code" style="width: 200px; height: auto;">
                    </div>
                <?php else: ?>
                    <div class="w-[200px] h-[200px] bg-gray-200 flex items-center justify-center mx-auto mb-4 rounded-civic">
                        <p class="text-sm text-gray-500">Upload GCash QR<br>to assets/images/</p>
                    </div>
                <?php endif; ?>
                <div class="text-sm text-gray-600 space-y-2">
                    <p><strong>Steps:</strong></p>
                    <p>1. Open your GCash app</p>
                    <p>2. Tap "Scan QR"</p>
                    <p>3. Scan the code above</p>
                    <p>4. Send exactly <strong>₱150.00</strong></p>
                    <p>5. Take a screenshot of confirmation</p>
                    <p>6. Upload the screenshot below ↓</p>
                </div>
            </div>

            <div>
                <label for="receipt" class="block text-sm font-medium text-gray-700 mb-2">Payment Receipt</label>
                <input type="file" id="receipt" name="receipt" accept=".pdf,.jpg,.jpeg,.png" required
                       class="w-full px-4 py-3 border border-gray-300 rounded-civic focus:ring-2 focus:ring-skyblue focus:border-transparent">
                <p class="text-xs text-gray-500 mt-1">Upload screenshot of GCash payment confirmation</p>
            </div>

            <button type="submit" class="w-full bg-skyblue hover:bg-blue-600 text-white font-semibold py-3 px-4 rounded-civic transition">
                Upload Receipt & Submit
            </button>
        </form>
    </div>

    <div class="mt-6 text-center">
        <a href="citizen_dashboard.php" class="text-skyblue hover:underline font-medium">← Back to Dashboard</a>
    </div>
</div>

<script src="qr_toggle.js"></script>
<?php require_once 'footer.php'; ?>
