<?php
session_start();
require_once __DIR__ . '/../config.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('HTTP/1.1 403 Forbidden');
    exit;
}

header('Content-Type: application/json');

$start = $_GET['start'] ?? '';
$end = $_GET['end'] ?? '';
$customer = $_GET['customer'] ?? '';
$sku = $_GET['sku'] ?? '';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "SELECT file_name, file_size, modification_time, file_path FROM log_files WHERE 1=1";
    $params = [];

    if (!empty($start)) {
        $sql .= " AND modification_time >= :start";
        $params[':start'] = $start;
    }
    if (!empty($end)) {
        $sql .= " AND modification_time <= :end";
        $params[':end'] = $end;
    }
    if (!empty($customer)) {
        $sql .= " AND customer = :customer";
        $params[':customer'] = $customer;
    }
    if (!empty($sku)) {
        $sql .= " AND sku = :sku";
        $params[':sku'] = $sku;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $logs = [];
    foreach ($results as $row) {
        $logs[] = [
            'name' => '<span class="file-name" data-path="' . htmlspecialchars($row['file_path']) . '">' . htmlspecialchars($row['file_name']) . '</span>',
            'size' => formatSizeUnits($row['file_size']),
            'modified' => $row['modification_time']
        ];
    }

    echo json_encode(['data' => $logs]);

} catch (PDOException $e) {
    echo json_encode(['data' => [], 'error' => 'Database error: ' . $e->getMessage()]);
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
