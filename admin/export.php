<?php
require_once __DIR__ . '/../auth.php';
redirectIfNotStaff();

$month = $_GET['month'] ?? date('Y-m');
$status_filter = $_GET['status'] ?? '';

$where = ["DATE_FORMAT(r.created_at, '%Y-%m') = ?"];
$params = [$month];

if ($status_filter) {
    $where[] = "r.status = ?";
    $params[] = $status_filter;
}

$whereClause = "WHERE " . implode(" AND ", $where);

$stmt = $pdo->prepare("
    SELECT r.id, r.tracking_number, u.full_name, u.email, u.contact,
           r.doc_type, r.status, r.delivery_address, r.created_at, 
           p.payment_method, p.amount
    FROM requests r
    JOIN users u ON r.user_id = u.id
    LEFT JOIN payments p ON r.id = p.request_id
    $whereClause
    ORDER BY r.created_at DESC
");
$stmt->execute($params);
$results = $stmt->fetchAll();

if (isset($_GET['download']) && $_GET['download'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="swiftcivic_report_' . $month . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    fputcsv($output, [
        'ID', 'Tracking #', 'Citizen Name', 'Email', 'Contact',
        'Document Type', 'Status', 'Delivery Address',
        'Date Applied', 'Payment Method', 'Amount'
    ]);
    
    foreach ($results as $row) {
        fputcsv($output, [
            $row['id'],
            $row['tracking_number'],
            $row['full_name'],
            $row['email'],
            $row['contact'],
            $row['doc_type'],
            $row['status'],
            $row['delivery_address'],
            $row['created_at'],
            $row['payment_method'],
            $row['amount']
        ]);
    }
    
    fclose($output);
    exit();
}

$pageTitle = 'Export Report';
require_once __DIR__ . '/header.php';
?>

<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 md:mb-8 gap-4">
    <h1 class="text-2xl md:text-3xl font-bold text-navy">Export Monthly Report</h1>
    <a href="verify.php" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-3 md:px-4 rounded-civic text-xs md:text-sm">
        Back to Verification
    </a>
</div>

<div class="bg-white rounded-civic shadow-lg p-4 md:p-6 mb-6 md:mb-8">
    <form method="GET" action="" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 items-end">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Month (YYYY-MM)</label>
            <input type="month" name="month" value="<?= htmlspecialchars($month) ?>"
                   class="w-full px-3 py-2 border border-gray-300 rounded-civic">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Filter by Status</label>
            <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-civic">
                <option value="">All Statuses</option>
                <option value="Awaiting Payment" <?= $status_filter === 'Awaiting Payment' ? 'selected' : '' ?>>Awaiting Payment</option>
                <option value="Verification Pending" <?= $status_filter === 'Verification Pending' ? 'selected' : '' ?>>Verification Pending</option>
                <option value="Processing" <?= $status_filter === 'Processing' ? 'selected' : '' ?>>Processing</option>
                <option value="Action Required" <?= $status_filter === 'Action Required' ? 'selected' : '' ?>>Action Required</option>
                <option value="Released" <?= $status_filter === 'Released' ? 'selected' : '' ?>>Released</option>
            </select>
        </div>
        <div class="flex flex-wrap gap-2">
            <button type="submit" class="flex-1 min-w-[80px] bg-navy hover:bg-blue-900 text-white font-bold py-2 px-3 md:px-4 rounded-civic text-xs md:text-sm">
                Filter
            </button>
            <a href="?month=<?= urlencode($month) ?>&status=<?= urlencode($status_filter) ?>&download=csv"
               class="flex-1 min-w-[80px] bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-3 md:px-4 rounded-civic text-xs md:text-sm text-center">
                Download CSV
            </a>
        </div>
    </form>
</div>

<div class="bg-white rounded-civic shadow-lg overflow-hidden">
    <div class="p-4 md:p-6 border-b">
        <h3 class="text-lg md:text-xl font-bold text-navy">Preview (<?= count($results) ?> records)</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-navy text-white">
                <tr>
                    <th class="px-4 py-3 text-left text-sm">Tracking #</th>
                    <th class="px-4 py-3 text-left text-sm">Citizen</th>
                    <th class="px-4 py-3 text-left text-sm">Document</th>
                    <th class="px-4 py-3 text-left text-sm">Status</th>
                    <th class="px-4 py-3 text-left text-sm">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php foreach ($results as $row): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm font-medium"><?= htmlspecialchars($row['tracking_number']) ?></td>
                        <td class="px-4 py-3 text-sm"><?= htmlspecialchars($row['full_name']) ?></td>
                        <td class="px-4 py-3 text-sm"><?= htmlspecialchars($row['doc_type']) ?></td>
                        <td class="px-4 py-3 text-sm">
                            <span class="px-2 py-1 rounded-full text-xs font-bold
                                <?php
                                switch($row['status']) {
                                    case 'Released': echo 'bg-green-100 text-green-800'; break;
                                    case 'Processing': echo 'bg-blue-100 text-blue-800'; break;
                                    case 'Action Required': echo 'bg-red-100 text-red-800'; break;
                                    default: echo 'bg-yellow-100 text-yellow-800';
                                }
                                ?>">
                                <?= htmlspecialchars($row['status']) ?>
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm"><?= date('M d, Y', strtotime($row['created_at'])) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
