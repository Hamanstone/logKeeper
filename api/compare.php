<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Check if paths are provided
if (!isset($_POST['path1']) || !isset($_POST['path2'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Two file paths required']);
    exit;
}

$path1 = $_POST['path1'];
$path2 = $_POST['path2'];

// Security check: prevent directory traversal
if (strpos($path1, '..') !== false || strpos($path2, '..') !== false) {
    http_response_code(403);
    echo json_encode(['error' => 'Invalid file path']);
    exit;
}

// Check if files exist
if (!file_exists($path1) || !is_file($path1)) {
    http_response_code(404);
    echo json_encode(['error' => 'File 1 not found']);
    exit;
}

if (!file_exists($path2) || !is_file($path2)) {
    http_response_code(404);
    echo json_encode(['error' => 'File 2 not found']);
    exit;
}

// Check file size (limit to 5MB for comparison)
$maxSize = 5 * 1024 * 1024; // 5MB
if (filesize($path1) > $maxSize || filesize($path2) > $maxSize) {
    http_response_code(413);
    echo json_encode(['error' => 'File too large for comparison (max 5MB)']);
    exit;
}

// Read file contents
$content1 = file_get_contents($path1);
$content2 = file_get_contents($path2);

// Check if files are binary
if (!mb_check_encoding($content1, 'UTF-8') || !mb_check_encoding($content2, 'UTF-8')) {
    http_response_code(415);
    echo json_encode(['error' => 'Binary files cannot be compared']);
    exit;
}

// Return file contents
header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'file1' => [
        'name' => basename($path1),
        'content' => $content1
    ],
    'file2' => [
        'name' => basename($path2),
        'content' => $content2
    ]
]);
