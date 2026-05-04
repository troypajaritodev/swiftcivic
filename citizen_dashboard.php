<?php
require_once 'auth.php';
redirectIfNotLoggedIn();

$user = getCurrentUser();

$stmt = $pdo->prepare("
    SELECT r.*, p.receipt_path, p.payment_method, p.status as payment_status
    FROM requests r
    LEFT JOIN payments p ON r.id = p.request_id
    WHERE r.user_id = ?
    ORDER BY r.created_at DESC
");
$stmt->execute([$user['id']]);
$requests = $stmt->fetchAll();

function getProgressBar($status) {
    $allStages = ['Awaiting Payment', 'Verification Pending', 'Processing', 'Released'];
    $stages = ['Awaiting Payment' => 1, 'Verification Pending' => 2, 'Action Required' => 2, 'Processing' => 3, 'Released' => 4];
    $current = $stages[$status] ?? 0;

    $html = '<div class="flex items-center w-full mb-4">';
    foreach ($allStages as $index => $stage) {
        $stageNum = $index + 1;
        $isActive = $stageNum <= $current;

        if (!$isActive) {
            $circleColor = 'bg-gray-200 text-gray-500';
        } elseif ($status === 'Released') {
            $circleColor = 'bg-green-500 text-white';
        } elseif ($status === 'Processing') {
            $circleColor = 'bg-blue-500 text-white';
        } elseif ($status === 'Action Required') {
            $circleColor = 'bg-red-500 text-white';
        } else {
            $circleColor = 'bg-skyblue text-white';
        }

        $html .= '<div class="flex-1 flex flex-col items-center">';
        $html .= '<div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold ' . $circleColor . '">' .
                 $stageNum . '</div>';
        $html .= '<span class="text-xs mt-1 text-center' .
                  ($status === 'Action Required' && $stage === 'Verification Pending' ? ' text-red-500 font-bold' : '') .
                  '">' . $stage . '</span>';
        $html .= '</div>';

        if ($index < count($allStages) - 1) {
            $lineColor = ($stageNum < $current) ?
                ($status === 'Released' ? 'bg-green-500' : ($status === 'Processing' ? 'bg-blue-500' : ($status === 'Action Required' ? 'bg-red-500' : 'bg-skyblue'))) :
                'bg-gray-200';
            $html .= '<div class="w-full h-1 ' . $lineColor . ' -mt-6"></div>';
        }
    }
    $html .= '</div>';
    return $html;
}

$pageTitle = 'My Dashboard';
require_once 'header.php';
?>

<div class="max-w-6xl mx-auto">
    <h1 class="text-2xl md:text-3xl font-bold text-navy mb-8">Welcome, <?= htmlspecialchars($user['full_name']) ?></h1>

    <?php if (isset($_GET['payment_success'])): ?>
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-civic mb-6">
            Payment receipt uploaded successfully! Your request is now pending verification.
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error']) && $_GET['error'] === 'invalid_status'): ?>
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-civic mb-6">
            This request is not in a valid status for payment upload.
        </div>
    <?php endif; ?>

    <!-- Stats Summary -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
        <div class="bg-white rounded-civic shadow p-4 text-center">
            <p class="text-2xl font-bold text-navy"><?= count($requests) ?></p>
            <p class="text-sm text-gray-600">Total Requests</p>
        </div>
        <div class="bg-white rounded-civic shadow p-4 text-center">
            <p class="text-2xl font-bold text-skyblue"><?= count(array_filter($requests, fn($r) => $r['status'] !== 'Released')) ?></p>
            <p class="text-sm text-gray-600">Active</p>
        </div>
        <div class="bg-white rounded-civic shadow p-4 text-center">
            <p class="text-2xl font-bold text-green-600"><?= count(array_filter($requests, fn($r) => $r['status'] === 'Released')) ?></p>
            <p class="text-sm text-gray-600">Completed</p>
        </div>
    </div>

    <!-- Requests List -->
    <div class="mb-8">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-bold text-navy">My Requests</h2>
            <a href="apply.php" class="bg-skyblue hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded-civic text-sm transition">
                New Application
            </a>
        </div>

        <?php if (empty($requests)): ?>
            <div class="bg-white rounded-civic shadow p-8 text-center">
                <p class="text-gray-600 mb-4">You haven't submitted any requests yet.</p>
                <a href="apply.php" class="text-skyblue hover:underline font-medium">Apply for a document now</a>
            </div>
        <?php else: ?>
            <div class="space-y-4">
                <?php foreach ($requests as $req): ?>
                    <div class="bg-white rounded-civic shadow p-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                            <div>
                                <p class="text-sm text-gray-600">Tracking Number</p>
                                <p class="font-bold text-navy"><?= htmlspecialchars($req['tracking_number']) ?></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Document Type</p>
                                <p class="font-bold text-navy"><?= htmlspecialchars($req['doc_type']) ?></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Status</p>
                                <span class="inline-block px-3 py-1 rounded-full text-xs font-bold
                                    <?php
                                    switch($req['status']) {
                                        case 'Released': echo 'bg-green-100 text-green-800'; break;
                                        case 'Processing': echo 'bg-blue-100 text-blue-800'; break;
                                        case 'Action Required': echo 'bg-red-100 text-red-800'; break;
                                        default: echo 'bg-yellow-100 text-yellow-800';
                                    }
                                    ?>">
                                    <?= htmlspecialchars($req['status']) ?>
                                </span>
                            </div>
                        </div>

                        <?= getProgressBar($req['status']) ?>

                        <?php if ($req['status'] === 'Awaiting Payment'): ?>
                            <a href="payment.php?request_id=<?= $req['id'] ?>" class="inline-block bg-skyblue hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded-civic text-sm">
                                Upload Payment Receipt
                            </a>
                            <a href="cancel_request.php?request_id=<?= $req['id'] ?>" class="inline-block bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-civic text-sm" onclick="return confirm('Are you sure you want to cancel this request? This action cannot be undone.')">
                                Cancel Request
                            </a>
                        <?php elseif ($req['status'] === 'Action Required'): ?>
                            <div class="bg-red-50 border border-red-200 p-4 rounded-civic mb-4">
                                <p class="text-red-800 font-medium">Action Required</p>
                                <?php if ($req['admin_notes']): ?>
                                    <p class="text-red-700 text-sm mt-1"><?= htmlspecialchars($req['admin_notes']) ?></p>
                                <?php endif; ?>
                            </div>
                            <a href="payment.php?request_id=<?= $req['id'] ?>" class="inline-block bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-civic text-sm">
                                Re-upload Receipt
                            </a>
                        <?php elseif ($req['status'] === 'Released'): ?>
                            <?php
                            $docStmt = $pdo->prepare("SELECT * FROM documents WHERE request_id = ?");
                            $docStmt->execute([$req['id']]);
                            $doc = $docStmt->fetch();
                            if ($doc):
                                // Use helper function for cross-platform path resolution
                                $docFullPath = getFullPath($doc['file_path']);
                                if (file_exists($docFullPath)): ?>
                                    <a href="download.php?file=<?= urlencode($doc['file_path']) ?>" class="inline-block bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-civic text-sm">
                                        Download Document
                                    </a>
                            <?php else: ?>
                                    <span class="text-red-500 text-sm">Document file not found (Path: <?= htmlspecialchars($docFullPath) ?>)</span>
                            <?php endif; endif; ?>
                        <?php endif; ?>

                        <div class="mt-4 text-sm text-gray-500">
                            Submitted: <?= date('M d, Y h:i A', strtotime($req['created_at'])) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'footer.php'; ?>

