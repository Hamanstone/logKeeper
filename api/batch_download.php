<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Check if paths are provided
if (!isset($_POST['paths']) || !is_array($_POST['paths']) || empty($_POST['paths'])) {
    http_response_code(400);
    echo json_encode(['error' => 'No files selected']);
    exit;
}

$paths = $_POST['paths'];

// Validate all paths
$validPaths = [];
foreach ($paths as $path) {
    // Security check: prevent directory traversal
    if (strpos($path, '..') !== false) {
        continue;
    }
    
    // Check if file exists
    if (file_exists($path) && is_file($path)) {
        $validPaths[] = $path;
    }
}

if (empty($validPaths)) {
    http_response_code(404);
    echo json_encode(['error' => 'No valid files found']);
    exit;
}

// If only one file, download it directly
if (count($validPaths) === 1) {
    $file = $validPaths[0];
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($file) . '"');
    header('Content-Length: ' . filesize($file));
    readfile($file);
    exit;
}

// Multiple files - create a ZIP archive
$zipFilename = 'logs_' . date('Ymd_His') . '.zip';
$zipPath = sys_get_temp_dir() . '/' . $zipFilename;

$zip = new ZipArchive();
if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
    http_response_code(500);
    echo json_encode(['error' => 'Could not create ZIP file']);
    exit;
}

// Add files to ZIP
foreach ($validPaths as $file) {
    $filename = basename($file);
    
    // Handle duplicate filenames by adding parent directory
    $parentDir = basename(dirname($file));
    $uniqueName = $parentDir . '_' . $filename;
    
    $zip->addFile($file, $uniqueName);
}

$zip->close();

// Send ZIP file to browser
header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="' . $zipFilename . '"');
header('Content-Length: ' . filesize($zipPath));
readfile($zipPath);

// Clean up temporary ZIP file
unlink($zipPath);
exit;
