<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('HTTP/1.1 403 Forbidden');
    exit;
}

require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');

$filePath = $_GET['path'] ?? '';
$offset = (int)($_GET['offset'] ?? 0);
$length = (int)($_GET['length'] ?? 4096);

// Security: Basic check for directory traversal.
if (strpos($filePath, '..') !== false) {
    echo json_encode(['error' => 'Invalid path']);
    exit;
}

$baseDir = realpath(__DIR__ . '/../uploads');
$fullPath = realpath($filePath);

// Security: Ensure the resolved path is within the base directory.
if ($fullPath === false || strpos($fullPath, $baseDir) !== 0) {
    echo json_encode(['error' => 'File not found']);
    exit;
}

if (file_exists($fullPath) && is_file($fullPath)) {
    $fileInfo = null;
    try {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $stmt = $pdo->prepare("SELECT file_name, file_size, modification_time FROM log_files WHERE file_path = :path");
        $stmt->execute([':path' => $fullPath]);
        $fileInfo = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Continue without file info, but log the error
        error_log("Database error in log_preview.php: " . $e->getMessage());
    }

    $handle = fopen($fullPath, 'rb');
    if ($handle) {
        fseek($handle, $offset);
        $content = fread($handle, $length);
        $current_pos = ftell($handle);
        fclose($handle);

        $file_size = filesize($fullPath);
        $has_more = $current_pos < $file_size;

        $response = [
            'content' => $content,
            'next_offset' => $current_pos,
            'has_more' => $has_more,
        ];

        if ($fileInfo) {
            $response['file_info'] = [
                'name' => $fileInfo['file_name'],
                'size' => formatSizeUnits($fileInfo['file_size']),
                'modified' => $fileInfo['modification_time']
            ];
        }

        echo json_encode($response);
    } else {
        echo json_encode(['error' => 'Could not open file']);
    }
} else {
    echo json_encode(['error' => 'File not found']);
}

function formatSizeUnits($bytes) {
    if ($bytes >= 1073741824) {
        $bytes = number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        $bytes = number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        $bytes = number_format($bytes / 1024, 2) . ' KB';
    } elseif ($bytes > 1) {
        $bytes = $bytes . ' bytes';
    } elseif ($bytes == 1) {
        $bytes = $bytes . ' byte';
    } else {
        $bytes = '0 bytes';
    }
    return $bytes;
}
