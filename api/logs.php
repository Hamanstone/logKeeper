<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('HTTP/1.1 403 Forbidden');
    exit;
}

require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');

$customer = $_GET['customer'] ?? '';
$sku = $_GET['sku'] ?? '';
$date = $_GET['date'] ?? '';

if (empty($customer) || empty($sku) || empty($date)) {
    echo json_encode(['data' => []]);
    exit;
}

// Convert date from Ymd to Y-m-d for database query
try {
    $log_date = DateTime::createFromFormat('Ymd', $date);
    if ($log_date === false) {
        throw new Exception("Invalid date format");
    }
    $db_date = $log_date->format('Y-m-d');
} catch (Exception $e) {
    echo json_encode(['data' => [], 'error' => 'Invalid date format.']);
    exit;
}

$logs = [];

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare(
        "SELECT file_name, file_size, modification_time, file_path " .
        "FROM log_files " .
        "WHERE customer = :customer AND sku = :sku AND log_date = :log_date " .
        "ORDER BY modification_time DESC"
    );

    $stmt->execute([
        ':customer' => $customer,
        ':sku' => $sku,
        ':log_date' => $db_date
    ]);

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $relativePath = str_replace(__DIR__ . '/../uploads/', '', $row['file_path']);
        $logs[] = [
            'name' => $row['file_name'],
            'size' => formatSizeUnits($row['file_size']),
            'modified' => $row['modification_time'],
            'actions' => '<button class="btn btn-sm btn-primary btn-preview" data-path="' . htmlspecialchars($row['file_path']) . '">Preview</button> ' .
                         '<a href="api/download.php?path=' . urlencode($row['file_path']) . '" class="btn btn-sm btn-secondary">Download</a>'
        ];
    }

} catch (PDOException $e) {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    exit;
}

echo json_encode(['data' => $logs]);

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
