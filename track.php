<?php
require_once __DIR__ . '/db.php';
$pageTitle = 'Track Request';
require_once 'header.php';

$tracking_number = '';
$request = null;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tracking_number = filter_input(INPUT_POST, 'tracking_number', FILTER_DEFAULT);
    
    if (empty($tracking_number)) {
        $error = 'Please enter a tracking number.';
    } else {
        $stmt = $pdo->prepare("
            SELECT r.*, u.full_name, u.email, u.contact
            FROM requests r
            JOIN users u ON r.user_id = u.id
            WHERE r.tracking_number = ?
        ");
        $stmt->execute([$tracking_number]);
        $request = $stmt->fetch();
        
        if (!$request) {
            $error = 'Tracking number not found.';
        }
    }
}

function getStageNumber($status) {
    $stages = [
        'Awaiting Payment' => 1,
        'Verification Pending' => 2,
        'Action Required' => 2,
        'Processing' => 3,
        'Released' => 4
    ];
    return $stages[$status] ?? 0;
}

function getStatusColor($status) {
    switch($status) {
        case 'Action Required': return 'bg-red-500';
        case 'Released': return 'bg-green-500';
        case 'Processing': return 'bg-blue-500';
        default: return 'bg-skyblue';
    }
}
?>

<div class="max-w-2xl mx-auto min-h-[calc(100vh-200px)]">
    <h1 class="text-3xl font-bold text-navy mb-8 text-center">Track Your Request</h1>
    
    <div class="bg-white rounded-civic shadow-lg p-8 mb-8">
        <form method="POST" action="" class="space-y-6">
            <div>
                <label for="tracking_number" class="block text-sm font-medium text-gray-700 mb-2">Tracking Number</label>
                <input type="text" id="tracking_number" name="tracking_number" required
                       class="w-full px-4 py-3 border border-gray-300 rounded-civic focus:ring-2 focus:ring-skyblue focus:border-transparent"
                       value="<?= htmlspecialchars($tracking_number) ?>"
                       placeholder="e.g., TRK123456789">
            </div>
            
            <button type="submit"
                    class="w-full bg-skyblue hover:bg-blue-600 text-white font-semibold py-3 px-4 rounded-civic transition duration-200">
                Track Request
            </button>
        </form>
        
        <?php if ($error): ?>
            <div class="mt-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-civic">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
    </div>
    
    <?php if ($request): ?>
    <div class="bg-white rounded-civic shadow-lg p-8">
        <h2 class="text-2xl font-bold text-navy mb-6">Request Details</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div>
                <p class="text-sm text-gray-600">Tracking Number</p>
                <p class="font-semibold"><?= htmlspecialchars($request['tracking_number']) ?></p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Status</p>
                <span class="px-3 py-1 rounded-full text-white text-sm font-bold <?= getStatusColor($request['status']) ?>">
                    <?= htmlspecialchars($request['status']) ?>
                </span>
            </div>
            <div>
                <p class="text-sm text-gray-600">Document Type</p>
                <p class="font-semibold"><?= htmlspecialchars($request['doc_type']) ?></p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Applicant</p>
                <p class="font-semibold"><?= htmlspecialchars($request['full_name']) ?></p>
            </div>
        </div>
        
        <div class="mb-6">
            <p class="text-sm text-gray-600 mb-2">Progress</p>
            <div class="flex items-center space-x-2">
                <?php 
                $currentStage = getStageNumber($request['status']);
                $stages = ['Awaiting Payment', 'Verification Pending', 'Processing', 'Released'];
                foreach ($stages as $index => $stage): 
                    $stageNum = $index + 1;
                    $isActive = $stageNum <= $currentStage;
                    $isCurrent = $stage === $request['status'];
                    $bgColor = $isCurrent && $stage === 'Action Required' ? 'bg-red-500' : 
                              ($isActive ? 'bg-skyblue' : 'bg-gray-300');
                ?>
                    <div class="flex-1 text-center">
                        <div class="w-8 h-8 mx-auto rounded-full <?= $bgColor ?> text-white flex items-center justify-center text-sm font-bold">
                            <?= $stageNum ?>
                        </div>
                        <p class="text-xs mt-1 <?= $isCurrent ? 'font-bold text-navy' : 'text-gray-600' ?>">
                            <?= $stage ?>
                        </p>
                    </div>
                    <?php if ($index < count($stages) - 1): ?>
                        <div class="w-8 h-1 <?= $stageNum < $currentStage ? 'bg-skyblue' : 'bg-gray-300' ?>"></div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="bg-gray-50 rounded-civic p-4">
            <p class="text-sm text-gray-600">Delivery Address</p>
            <p class="font-semibold"><?= htmlspecialchars($request['delivery_address']) ?></p>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php require_once 'footer.php'; ?>
