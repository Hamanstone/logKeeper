<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('HTTP/1.1 403 Forbidden');
    exit;
}

$filePath = $_GET['path'] ?? '';

// Security: Basic check for directory traversal.
if (strpos($filePath, '..') !== false) {
    header('HTTP/1.1 400 Bad Request');
    exit;
}

$baseDir = realpath(__DIR__ . '/../uploads');
$fullPath = realpath($filePath);

// Security: Ensure the resolved path is within the base directory.
if ($fullPath === false || strpos($fullPath, $baseDir) !== 0) {
    header('HTTP/1.1 404 Not Found');
    exit;
}

if (file_exists($fullPath) && is_file($fullPath)) {
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($fullPath) . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($fullPath));
    readfile($fullPath);
    exit;
} else {
    header('HTTP/1.1 404 Not Found');
    exit;
}
