<?php
require_once 'auth.php';
redirectIfNotLoggedIn();

if (!isset($_GET['file'])) {
    header('Location: citizen_dashboard.php');
    exit();
}

$file_path = urldecode($_GET['file']);

// Use helper function for cross-platform path resolution
$fullPath = getFullPath($file_path);

// Security check: ensure file is within uploads directory
$realPath = realpath($fullPath);
$uploadsDir = realpath(getFullPath('uploads'));

if ($realPath === false || $uploadsDir === false || strpos($realPath, $uploadsDir) !== 0) {
    http_response_code(403);
    die('Access denied');
}

if (!file_exists($realPath)) {
    http_response_code(404);
    die('File not found: ' . htmlspecialchars($realPath));
}

// Get file info
$mimeType = mime_content_type($realPath);
$fileName = basename($realPath);

// Set headers for download
header('Content-Type: ' . $mimeType);
header('Content-Disposition: attachment; filename="' . $fileName . '"');
header('Content-Length: ' . filesize($realPath));
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');

// Output file
readfile($realPath);
exit();
