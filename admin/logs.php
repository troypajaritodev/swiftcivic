<?php
require_once __DIR__ . '/../auth.php';
redirectIfNotStaff();

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 50;
$offset = ($page - 1) * $limit;

$user_filter = $_GET['user'] ?? '';
$action_filter = $_GET['action'] ?? '';
$request_filter = $_GET['request'] ?? '';

$where = [];
$params = [];

if ($user_filter) {
    $where[] = "u.full_name LIKE ?";
    $params[] = "%$user_filter%";
}
if ($action_filter) {
    $where[] = "l.action LIKE ?";
    $params[] = "%$action_filter%";
}
if ($request_filter) {
    $where[] = "l.request_id = ?";
    $params[] = $request_filter;
}

$whereClause = $where ? "WHERE " . implode(" AND ", $where) : "";

$countStmt = $pdo->prepare("
    SELECT COUNT(*) as total
    FROM logs l
    LEFT JOIN users u ON l.user_id = u.id
    LEFT JOIN requests r ON l.request_id = r.id
    $whereClause
");
$countStmt->execute($params);
$total = $countStmt->fetch()['total'];
$totalPages = ceil($total / $limit);

$stmt = $pdo->prepare("
    SELECT l.*, u.full_name, u.email, r.tracking_number
    FROM logs l
    LEFT JOIN users u ON l.user_id = u.id
    LEFT JOIN requests r ON l.request_id = r.id
    $whereClause
    ORDER BY l.created_at DESC
    LIMIT ? OFFSET ?
");
$params[] = $limit;
$params[] = $offset;
$stmt->execute($params);
$logs = $stmt->fetchAll();

$pageTitle = 'Audit Logs';
require_once __DIR__ . '/header.php';
?>

<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 md:mb-8 gap-4">
    <h1 class="text-2xl md:text-3xl font-bold text-navy">Audit Logs</h1>
    <a href="export.php" class="bg-skyblue hover:bg-blue-600 text-white font-bold py-2 px-3 md:px-4 rounded-civic text-xs md:text-sm">
        Export CSV
    </a>
</div>

<div class="bg-white rounded-civic shadow-lg p-6 mb-8">
    <form method="GET" action="" class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Filter by User</label>
            <input type="text" name="user" value="<?= htmlspecialchars($user_filter) ?>"
                   class="w-full px-3 py-2 border border-gray-300 rounded-civic text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Filter by Action</label>
            <input type="text" name="action" value="<?= htmlspecialchars($action_filter) ?>"
                   class="w-full px-3 py-2 border border-gray-300 rounded-civic text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Filter by Request ID</label>
            <input type="text" name="request" value="<?= htmlspecialchars($request_filter) ?>"
                   class="w-full px-3 py-2 border border-gray-300 rounded-civic text-sm">
        </div>
        <div class="flex items-end">
            <button type="submit" class="w-full bg-navy hover:bg-blue-900 text-white font-bold py-2 px-4 rounded-civic text-sm">
                Filter
            </button>
        </div>
    </form>
</div>

<div class="bg-white rounded-civic shadow-lg overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-navy text-white">
                <tr>
                    <th class="px-4 py-3 text-left text-sm">Timestamp</th>
                    <th class="px-4 py-3 text-left text-sm">User</th>
                    <th class="px-4 py-3 text-left text-sm">Action</th>
                    <th class="px-4 py-3 text-left text-sm">Request</th>
                    <th class="px-4 py-3 text-left text-sm">IP Address</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php foreach ($logs as $log): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm"><?= date('M d, Y h:i A', strtotime($log['created_at'])) ?></td>
                        <td class="px-4 py-3 text-sm">
                            <?= htmlspecialchars($log['full_name'] ?? 'System') ?>
                            <br><span class="text-xs text-gray-500"><?= htmlspecialchars($log['email'] ?? '') ?></span>
                        </td>
                        <td class="px-4 py-3 text-sm"><?= htmlspecialchars($log['action']) ?></td>
                        <td class="px-4 py-3 text-sm">
                            <?php if ($log['tracking_number']): ?>
                                <a href="verify.php?id=<?= $log['request_id'] ?>" class="text-skyblue hover:underline"><?= htmlspecialchars($log['tracking_number']) ?></a>
                            <?php else: ?>
                                <span class="text-gray-400">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-500"><?= htmlspecialchars($log['ip_address'] ?? 'N/A') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if ($totalPages > 1): ?>
        <div class="px-4 py-3 border-t flex justify-between items-center">
            <p class="text-sm text-gray-600">Showing <?= $offset + 1 ?>-<?= min($offset + $limit, $total) ?> of <?= $total ?></p>
            <div class="flex gap-2">
                <?php if ($page > 1): ?>
                    <a href="?page=<?= $page - 1 ?>&user=<?= urlencode($user_filter) ?>&action=<?= urlencode($action_filter) ?>&request=<?= urlencode($request_filter) ?>"
                       class="px-3 py-1 bg-gray-200 hover:bg-gray-300 rounded-civic text-sm">Previous</a>
                <?php endif; ?>
                <?php if ($page < $totalPages): ?>
                    <a href="?page=<?= $page + 1 ?>&user=<?= urlencode($user_filter) ?>&action=<?= urlencode($action_filter) ?>&request=<?= urlencode($request_filter) ?>"
                       class="px-3 py-1 bg-gray-200 hover:bg-gray-300 rounded-civic text-sm">Next</a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
