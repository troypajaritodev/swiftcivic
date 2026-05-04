<?php
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../mailer.php';
redirectIfNotStaff();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $request_id = (int)$_POST['request_id'];
    $action = $_POST['action'] ?? '';
    $admin_notes = filter_input(INPUT_POST, 'admin_notes', FILTER_DEFAULT);

    $stmt = $pdo->prepare("SELECT * FROM requests WHERE id = ?");
    $stmt->execute([$request_id]);
    $request = $stmt->fetch();

    if (!$request) {
        $error = 'Request not found.';
    } else {
        $pdo->beginTransaction();
        try {
            $newStatus = $request['status'];

            if ($action === 'approve') {
                $stmt = $pdo->prepare("UPDATE requests SET status = 'Processing', admin_notes = NULL WHERE id = ?");
                $stmt->execute([$request_id]);
                $newStatus = 'Processing';
                logAction("Approved payment for request #$request_id", $request_id);

                $stmt = $pdo->prepare("UPDATE payments SET status = 'Verified', verified_by = ?, verified_at = NOW() WHERE request_id = ?");
                $stmt->execute([$_SESSION['user_id'], $request_id]);

            } elseif ($action === 'reject') {
                $stmt = $pdo->prepare("UPDATE requests SET status = 'Action Required', admin_notes = ? WHERE id = ?");
                $stmt->execute([$admin_notes ?: 'Payment receipt rejected. Please upload a valid receipt.', $request_id]);
                $newStatus = 'Action Required';
                logAction("Rejected payment for request #$request_id", $request_id);

                $stmt = $pdo->prepare("UPDATE payments SET status = 'Rejected' WHERE request_id = ?");
                $stmt->execute([$request_id]);

            } elseif ($action === 'release') {
                if (!isset($_FILES['document']) || $_FILES['document']['error'] !== UPLOAD_ERR_OK) {
                    throw new Exception('Please upload the released document.');
                }

                $file = $_FILES['document'];
                $allowedTypes = ['application/pdf', 'image/jpeg', 'image/png', 'image/jpg'];
                $maxSize = 10 * 1024 * 1024;

                if (!in_array($file['type'], $allowedTypes)) {
                    throw new Exception('Invalid file type. Only PDF, JPG, PNG allowed.');
                }
                if ($file['size'] > $maxSize) {
                    throw new Exception('File too large. Maximum 10MB allowed.');
                }

                $uploadDir = __DIR__ . '/../uploads/documents/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $filename = uniqid('doc_', true) . '.' . $ext;
                $filepath = $uploadDir . $filename;

                if (!move_uploaded_file($file['tmp_name'], $filepath)) {
                    throw new Exception('Failed to upload document.');
                }

                $stmt = $pdo->prepare("
                    INSERT INTO documents (request_id, file_path, file_name, uploaded_by)
                    VALUES (?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE file_path = VALUES(file_path), file_name = VALUES(file_name), uploaded_by = VALUES(uploaded_by)
                ");
                $stmt->execute([$request_id, 'uploads/documents/' . $filename, $file['name'], $_SESSION['user_id']]);

                $stmt = $pdo->prepare("UPDATE requests SET status = 'Released', admin_notes = NULL WHERE id = ?");
                $stmt->execute([$request_id]);
                $newStatus = 'Released';
                logAction("Released document for request #$request_id", $request_id);
            }

            $pdo->commit();

            // Send email notification
            if (in_array($newStatus, ['Processing', 'Action Required', 'Released'])) {
                sendStatusEmail($request_id, $newStatus);
            }

            if ($action === 'release') {
                $success = 'Document released successfully! Email notification sent.';
            } elseif ($action === 'approve') {
                $success = 'Payment approved! Email notification sent.';
            } elseif ($action === 'reject') {
                $success = 'Payment rejected! User has been notified.';
            }

        } catch (Exception $e) {
            $pdo->rollBack();
            $error = $e->getMessage();
        }
    }
}

$status_filter = $_GET['status'] ?? 'Verification Pending';

$stmt = $pdo->prepare("
    SELECT r.*, u.full_name, u.email, u.contact, p.receipt_path, p.payment_method, p.status as payment_status
    FROM requests r
    JOIN users u ON r.user_id = u.id
    LEFT JOIN payments p ON r.id = p.request_id
    WHERE r.status = ?
    ORDER BY r.created_at ASC
");
$stmt->execute([$status_filter]);
$pendingRequests = $stmt->fetchAll();

$pageTitle = 'Verification Hub';
require_once __DIR__ . '/header.php';
?>

<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 md:mb-8 gap-4">
    <h1 class="text-2xl md:text-3xl font-bold text-navy">Verification Hub</h1>
    <div class="flex flex-wrap gap-2">
        <a href="?status=Verification Pending" class="px-3 py-1.5 md:px-4 md:py-2 rounded-civic text-xs md:text-sm <?= $status_filter === 'Verification Pending' ? 'bg-skyblue text-white' : 'bg-white text-navy' ?>">Pending</a>
        <a href="?status=Processing" class="px-3 py-1.5 md:px-4 md:py-2 rounded-civic text-xs md:text-sm <?= $status_filter === 'Processing' ? 'bg-skyblue text-white' : 'bg-white text-navy' ?>">Processing</a>
        <a href="?status=Action Required" class="px-3 py-1.5 md:px-4 md:py-2 rounded-civic text-xs md:text-sm <?= $status_filter === 'Action Required' ? 'bg-skyblue text-white' : 'bg-white text-navy' ?>">Action Required</a>
    </div>
</div>

<?php if ($error): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-civic mb-6"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>
<?php if ($success): ?>
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-civic mb-6"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<?php if (empty($pendingRequests)): ?>
    <div class="bg-white rounded-civic shadow-lg p-8 text-center">
        <p class="text-gray-600">No requests found with status: <?= htmlspecialchars($status_filter) ?></p>
    </div>
<?php else: ?>
    <div class="space-y-6 md:space-y-8">
        <?php foreach ($pendingRequests as $req): ?>
            <div class="bg-white rounded-civic shadow-lg p-4 md:p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 md:gap-8">
                    <div>
                        <h3 class="text-lg md:text-xl font-bold text-navy mb-4">Application Details</h3>
                        <div class="space-y-3">
                            <div>
                                <p class="text-sm text-gray-600">Tracking Number</p>
                                <p class="font-bold"><?= htmlspecialchars($req['tracking_number']) ?></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Applicant</p>
                                <p class="font-bold"><?= htmlspecialchars($req['full_name']) ?></p>
                                <p class="text-sm text-gray-500"><?= htmlspecialchars($req['email']) ?> | <?= htmlspecialchars($req['contact'] ?? 'No contact') ?></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Document Type</p>
                                <p class="font-bold"><?= htmlspecialchars($req['doc_type']) ?></p>
                            </div>
                            <?php if ($req['purpose']): ?>
                                <div>
                                    <p class="text-sm text-gray-600">Purpose</p>
                                    <p><?= htmlspecialchars($req['purpose']) ?></p>
                                </div>
                            <?php endif; ?>
                            <?php if ($req['valid_id_path']): ?>
                                <div>
                                    <p class="text-sm text-gray-600">Valid ID</p>
                                    <?php
                                    $idPath = getFullPath($req['valid_id_path']);
                                    if (file_exists($idPath)): ?>
                                        <a href="/<?= htmlspecialchars($req['valid_id_path']) ?>" target="_blank" class="text-skyblue hover:underline">View ID</a>
                                        <span class="text-xs text-gray-500 ml-2">(<?= htmlspecialchars($req['valid_id_path']) ?>)</span>
                                    <?php else: ?>
                                        <span class="text-red-500 text-sm">File not found: <?= htmlspecialchars($req['valid_id_path']) ?></span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-lg md:text-xl font-bold text-navy mb-4">Payment Receipt</h3>
                            <?php if ($req['receipt_path']): ?>
                                <div class="mb-4">
                                    <?php
                                    $receiptPath = getFullPath($req['receipt_path']);
                                    if (file_exists($receiptPath) && in_array(pathinfo($req['receipt_path'], PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png'])): ?>
                                        <img src="/<?= htmlspecialchars($req['receipt_path']) ?>" alt="Receipt" class="max-w-full rounded-civic border">
                                    <?php elseif (file_exists($receiptPath)): ?>
                                        <a href="/<?= htmlspecialchars($req['receipt_path']) ?>" target="_blank" class="inline-block bg-gray-100 hover:bg-gray-200 px-4 py-2 rounded-civic">
                                            View PDF Receipt
                                        </a>
                                    <?php else: ?>
                                        <span class="text-red-500 text-sm">Receipt file not found: <?= htmlspecialchars($req['receipt_path']) ?></span>
                                    <?php endif; ?>
                                    <p class="text-sm text-gray-600 mt-2">Method: <?= htmlspecialchars($req['payment_method']) ?></p>
                                </div>
                            <?php else: ?>
                            <p class="text-gray-500 mb-4">No receipt uploaded yet.</p>
                        <?php endif; ?>

                        <form method="POST" action="" enctype="multipart/form-data" class="space-y-4">
                            <input type="hidden" name="request_id" value="<?= $req['id'] ?>">

                            <div class="flex flex-wrap gap-2">
                                <button type="submit" name="action" value="approve" onclick="return confirm('Approve this payment and move to Processing?')"
                                        class="flex-1 min-w-[120px] bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-3 md:px-4 rounded-civic text-xs md:text-sm">
                                    Approve & Process
                                </button>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Reject Reason (if rejecting)</label>
                                <input type="text" name="admin_notes" placeholder="Enter reason..."
                                       class="w-full px-3 py-2 border border-gray-300 rounded-civic text-sm">
                            </div>
                            <button type="submit" name="action" value="reject" onclick="return confirm('Reject this payment? User will need to re-upload.')"
                                    class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-3 md:px-4 rounded-civic text-xs md:text-sm">
                                Reject Payment
                            </button>

                            <?php if ($req['status'] === 'Processing'): ?>
                                <div class="border-t pt-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Upload Released Document (PDF/Image)</label>
                                    <input type="file" name="document" accept=".pdf,.jpg,.jpeg,.png" class="w-full text-sm mb-2">
                                    <button type="submit" name="action" value="release" onclick="return confirm('Release this document to the citizen?')"
                                            class="w-full bg-skyblue hover:bg-blue-600 text-white font-bold py-2 px-3 md:px-4 rounded-civic text-xs md:text-sm">
                                        Release Document
                                    </button>
                                </div>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/footer.php'; ?>
