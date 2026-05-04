<?php
require_once __DIR__ . '/../auth.php';
redirectIfNotStaff();

$statsStmt = $pdo->prepare("
    SELECT
        COUNT(*) as total,
        SUM(status = 'Awaiting Payment') as awaiting_payment,
        SUM(status = 'Verification Pending') as verification_pending,
        SUM(status = 'Processing') as processing,
        SUM(status = 'Action Required') as action_required,
        SUM(status = 'Released') as released
    FROM requests
");
$statsStmt->execute();
$stats = $statsStmt->fetch();

$recentStmt = $pdo->prepare("
    SELECT r.*, u.full_name
    FROM requests r
    JOIN users u ON r.user_id = u.id
    ORDER BY r.created_at DESC
    LIMIT 10
");
$recentStmt->execute();
$recent = $recentStmt->fetchAll();

$pageTitle = 'Admin Dashboard';
require_once __DIR__ . '/header.php';
?>

<div class="mb-6 md:mb-8">
    <h1 class="text-2xl md:text-3xl font-bold text-navy">Admin Dashboard</h1>
    <p class="text-sm md:text-base text-gray-600">Overview of all civil registry requests</p>
</div>

    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 md:gap-4 mb-6 md:mb-8">
    <div class="bg-white rounded-civic shadow-lg p-3 md:p-4 text-center">
        <p class="text-2xl md:text-3xl font-bold text-navy"><?= $stats['total'] ?></p>
        <p class="text-xs md:text-sm text-gray-600">Total</p>
    </div>
    <div class="bg-white rounded-civic shadow-lg p-3 md:p-4 text-center">
        <p class="text-2xl md:text-3xl font-bold text-yellow-600"><?= $stats['awaiting_payment'] ?></p>
        <p class="text-xs md:text-sm text-gray-600">Awaiting Payment</p>
    </div>
    <div class="bg-white rounded-civic shadow-lg p-3 md:p-4 text-center">
        <p class="text-2xl md:text-3xl font-bold text-orange-500"><?= $stats['verification_pending'] ?></p>
        <p class="text-xs md:text-sm text-gray-600">Pending Verification</p>
    </div>
    <div class="bg-white rounded-civic shadow-lg p-3 md:p-4 text-center">
        <p class="text-2xl md:text-3xl font-bold text-blue-600"><?= $stats['processing'] ?></p>
        <p class="text-xs md:text-sm text-gray-600">Processing</p>
    </div>
    <div class="bg-white rounded-civic shadow-lg p-3 md:p-4 text-center">
        <p class="text-2xl md:text-3xl font-bold text-red-600"><?= $stats['action_required'] ?></p>
        <p class="text-xs md:text-sm text-gray-600">Action Required</p>
    </div>
    <div class="bg-white rounded-civic shadow-lg p-3 md:p-4 text-center">
        <p class="text-2xl md:text-3xl font-bold text-green-600"><?= $stats['released'] ?></p>
        <p class="text-xs md:text-sm text-gray-600">Released</p>
    </div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6 mb-6 md:mb-8">
    <a href="verify.php" class="bg-white rounded-civic shadow-lg p-4 md:p-6 hover:shadow-xl transition">
        <h3 class="text-lg md:text-xl font-bold text-navy mb-2">Verification Hub</h3>
        <p class="text-sm text-gray-600">Review and verify payment receipts</p>
    </a>
    <a href="logs.php" class="bg-white rounded-civic shadow-lg p-4 md:p-6 hover:shadow-xl transition">
        <h3 class="text-lg md:text-xl font-bold text-navy mb-2">Audit Logs</h3>
        <p class="text-sm text-gray-600">View all system activity</p>
    </a>
    <a href="export.php" class="bg-white rounded-civic shadow-lg p-4 md:p-6 hover:shadow-xl transition sm:col-span-2 lg:col-span-1">
        <h3 class="text-lg md:text-xl font-bold text-navy mb-2">Export Reports</h3>
        <p class="text-sm text-gray-600">Download CSV reports</p>
    </a>
</div>

    <div class="bg-white rounded-civic shadow-lg p-4 md:p-6">
        <h2 class="text-lg md:text-xl font-bold text-navy mb-4">Recent Requests</h2>
        <!-- Mobile Card View (visible on small screens) -->
        <div class="block md:hidden space-y-4">
            <?php foreach ($recent as $req): ?>
                <div class="border border-gray-200 rounded-civic p-4">
                    <div class="flex justify-between items-start mb-2">
                        <p class="font-bold text-navy"><?= htmlspecialchars($req['tracking_number']) ?></p>
                        <span class="inline-block px-2 py-1 rounded-full text-xs font-bold
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
                    <p class="text-sm text-gray-600"><?= htmlspecialchars($req['full_name']) ?></p>
                    <p class="text-sm text-gray-500"><?= htmlspecialchars($req['doc_type']) ?></p>
                    <p class="text-xs text-gray-400 mt-1"><?= date('M d, Y', strtotime($req['created_at'])) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Desktop Table View (hidden on small screens) -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-sm">Tracking #</th>
                        <th class="px-4 py-2 text-left text-sm">Citizen</th>
                        <th class="px-4 py-2 text-left text-sm">Document</th>
                        <th class="px-4 py-2 text-left text-sm">Status</th>
                        <th class="px-4 py-2 text-left text-sm">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <?php foreach ($recent as $req): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 text-sm font-medium"><?= htmlspecialchars($req['tracking_number']) ?></td>
                            <td class="px-4 py-2 text-sm"><?= htmlspecialchars($req['full_name']) ?></td>
                            <td class="px-4 py-2 text-sm"><?= htmlspecialchars($req['doc_type']) ?></td>
                            <td class="px-4 py-2 text-sm">
                                <span class="inline-block px-2 py-1 rounded-full text-xs font-bold
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
                            </td>
                            <td class="px-4 py-2 text-sm text-gray-500"><?= date('M d, Y', strtotime($req['created_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
