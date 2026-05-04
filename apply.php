<?php
require_once 'auth.php';
redirectIfNotLoggedIn();

$user = getCurrentUser();
$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;
$error = '';
$success = '';

// Prevent step-skipping
if ($step === 2 && !isset($_SESSION['apply_doc_type'])) {
    header('Location: apply.php?step=1');
    exit();
}
if ($step === 3 && (!isset($_SESSION['apply_doc_type']))) {
    header('Location: apply.php?step=1');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($step === 1) {
        $doc_type = filter_input(INPUT_POST, 'doc_type', FILTER_DEFAULT);
        $purpose = filter_input(INPUT_POST, 'purpose', FILTER_DEFAULT);
        $delivery_address = filter_input(INPUT_POST, 'delivery_address', FILTER_DEFAULT);

        if (empty($doc_type)) {
            $error = 'Please select a document type.';
        } else {
            $_SESSION['apply_doc_type'] = $doc_type;
            $_SESSION['apply_purpose'] = $purpose;
            $_SESSION['apply_delivery_address'] = $delivery_address;
            header('Location: apply.php?step=2');
            exit();
        }
    } elseif ($step === 2) {
        if (!isset($_FILES['valid_id']) || $_FILES['valid_id']['error'] !== UPLOAD_ERR_OK) {
            $error = 'Please upload your valid ID.';
        } else {
            $file = $_FILES['valid_id'];
            $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
            $maxSize = 5 * 1024 * 1024;

            if (!in_array($file['type'], $allowedTypes)) {
                $error = 'Invalid file type. Only JPG and PNG are allowed.';
            } elseif ($file['size'] > $maxSize) {
                $error = 'File too large. Maximum 5MB allowed.';
            } else {
                $uploadDir = getFullPath('uploads/ids/');
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $filename = uniqid('id_', true) . '.' . $ext;
                $filepath = $uploadDir . $filename;

                if (move_uploaded_file($file['tmp_name'], $filepath)) {
                    $_SESSION['apply_valid_id_path'] = 'uploads/ids/' . $filename;
                    header('Location: apply.php?step=3');
                    exit();
                } else {
                    $error = 'Failed to upload ID.';
                }
            }
        }
    } elseif ($step === 3) {
        // Submit application
        $tracking_number = 'SWC-' . time() . '-' . rand(100, 999);

        $stmt = $pdo->prepare("
            INSERT INTO requests (user_id, tracking_number, doc_type, delivery_address, id_path, status, created_at)
            VALUES (?, ?, ?, ?, ?, 'Awaiting Payment', NOW())
        ");
        if ($stmt->execute([$user['id'], $tracking_number, $_SESSION['apply_doc_type'], $_SESSION['apply_delivery_address'], $_SESSION['apply_valid_id_path']])) {
            $request_id = $pdo->lastInsertId();

            logAction($user['id'], 'Application submitted', 'Request ID: ' . $request_id);

            unset($_SESSION['apply_doc_type'], $_SESSION['apply_purpose'], $_SESSION['apply_delivery_address'], $_SESSION['apply_valid_id_path']);

            header('Location: payment.php?request_id=' . $request_id);
            exit();
        } else {
            $error = 'Failed to submit application.';
        }
    }
}

$pageTitle = 'Apply for Document - Step ' . $step;
require_once 'header.php';
?>

<div class="max-w-2xl mx-auto">
    <div class="mb-8">
        <h1 class="text-2xl md:text-3xl font-bold text-navy mb-2">Apply for Document</h1>
        <div class="w-12 h-1 bg-skyblue"></div>
    </div>

    <!-- Progress Steps -->
    <div class="flex items-center mb-8">
        <div class="flex-1 text-center">
            <div class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold <?= $step >= 1 ? 'bg-navy text-white' : 'bg-gray-200 text-gray-500' ?> mx-auto">1</div>
            <p class="text-xs mt-2">Document Info</p>
        </div>
        <div class="flex-1 h-1 <?= $step >= 2 ? 'bg-navy' : 'bg-gray-200' ?>"></div>
        <div class="flex-1 text-center">
            <div class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold <?= $step >= 2 ? 'bg-navy text-white' : 'bg-gray-200 text-gray-500' ?> mx-auto">2</div>
            <p class="text-xs mt-2">Upload ID</p>
        </div>
        <div class="flex-1 h-1 <?= $step >= 3 ? 'bg-navy' : 'bg-gray-200' ?>"></div>
        <div class="flex-1 text-center">
            <div class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold <?= $step >= 3 ? 'bg-navy text-white' : 'bg-gray-200 text-gray-500' ?> mx-auto">3</div>
            <p class="text-xs mt-2">Submit</p>
        </div>
    </div>

    <?php if ($error): ?>
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-civic mb-6">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <div class="bg-white rounded-civic shadow-lg p-8">
        <?php if ($step === 1): ?>
            <h2 class="text-xl font-bold text-navy mb-6">Step 1: Document Information</h2>
            <form method="POST" action="" class="space-y-6">
                <div>
                    <label for="doc_type" class="block text-sm font-medium text-gray-700 mb-2">Document Type</label>
                    <select id="doc_type" name="doc_type" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-civic focus:ring-2 focus:ring-skyblue focus:border-transparent">
                        <option value="">Select document type...</option>
                        <option value="Birth Certificate" <?= ($_SESSION['apply_doc_type'] ?? '') === 'Birth Certificate' ? 'selected' : '' ?>>Birth Certificate</option>
                        <option value="Marriage Certificate" <?= ($_SESSION['apply_doc_type'] ?? '') === 'Marriage Certificate' ? 'selected' : '' ?>>Marriage Certificate</option>
                        <option value="Death Certificate" <?= ($_SESSION['apply_doc_type'] ?? '') === 'Death Certificate' ? 'selected' : '' ?>>Death Certificate</option>
                        <option value="Other" <?= ($_SESSION['apply_doc_type'] ?? '') === 'Other' ? 'selected' : '' ?>>Other</option>
                    </select>
                </div>

                <div>
                    <label for="purpose" class="block text-sm font-medium text-gray-700 mb-2">Purpose</label>
                    <input type="text" id="purpose" name="purpose"
                           class="w-full px-4 py-3 border border-gray-300 rounded-civic focus:ring-2 focus:ring-skyblue focus:border-transparent"
                           value="<?= htmlspecialchars($_SESSION['apply_purpose'] ?? '') ?>">
                </div>

                <div>
                    <label for="delivery_address" class="block text-sm font-medium text-gray-700 mb-2">Delivery Address</label>
                    <textarea id="delivery_address" name="delivery_address" rows="3"
                              class="w-full px-4 py-3 border border-gray-300 rounded-civic focus:ring-2 focus:ring-skyblue focus:border-transparent"><?= htmlspecialchars($_SESSION['apply_delivery_address'] ?? '') ?></textarea>
                </div>

                <button type="submit" class="w-full bg-navy hover:bg-blue-900 text-white font-semibold py-3 px-4 rounded-civic transition">
                    Next Step
                </button>
            </form>

        <?php elseif ($step === 2): ?>
            <h2 class="text-xl font-bold text-navy mb-6">Step 2: Upload Valid ID</h2>
            <form method="POST" action="" enctype="multipart/form-data" class="space-y-6">
                <div>
                    <label for="valid_id" class="block text-sm font-medium text-gray-700 mb-2">Valid ID</label>
                    <input type="file" id="valid_id" name="valid_id" accept=".jpg,.jpeg,.png" required
                           class="w-full px-4 py-3 border border-gray-300 rounded-civic focus:ring-2 focus:ring-skyblue focus:border-transparent">
                    <p class="text-xs text-gray-500 mt-1">Accepted formats: JPG, PNG. Maximum 5MB.</p>
                </div>

                <button type="submit" class="w-full bg-navy hover:bg-blue-900 text-white font-semibold py-3 px-4 rounded-civic transition">
                    Upload & Continue
                </button>
            </form>

        <?php elseif ($step === 3): ?>
            <h2 class="text-xl font-bold text-navy mb-6">Step 3: Confirm & Submit</h2>
            <div class="space-y-4 mb-6">
                <div class="bg-light-gray p-4 rounded-civic">
                    <p class="text-sm text-gray-600">Document Type</p>
                    <p class="font-bold text-navy"><?= htmlspecialchars($_SESSION['apply_doc_type'] ?? '') ?></p>
                </div>
                <div class="bg-light-gray p-4 rounded-civic">
                    <p class="text-sm text-gray-600">Purpose</p>
                    <p class="font-bold text-navy"><?= htmlspecialchars($_SESSION['apply_purpose'] ?? 'Not specified') ?></p>
                </div>
                <div class="bg-light-gray p-4 rounded-civic">
                    <p class="text-sm text-gray-600">Delivery Address</p>
                    <p class="font-bold text-navy"><?= htmlspecialchars($_SESSION['apply_delivery_address'] ?? 'Not specified') ?></p>
                </div>
                <div class="bg-light-gray p-4 rounded-civic">
                    <p class="text-sm text-gray-600">Valid ID</p>
                    <p class="font-bold text-navy text-green-600">✓ Uploaded</p>
                </div>
            </div>

            <form method="POST" action="">
                <button type="submit" class="w-full bg-skyblue hover:bg-blue-600 text-white font-semibold py-3 px-4 rounded-civic transition">
                    Submit Application
                </button>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'footer.php'; ?>
