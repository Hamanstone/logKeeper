<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('HTTP/1.1 403 Forbidden');
    exit;
}

require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');

$groups = [];

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->query(
        "SELECT DISTINCT customer, sku, DATE_FORMAT(log_date, '%Y%m%d') as log_date " .
        "FROM log_files " .
        "ORDER BY customer, sku, log_date"
    );

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $customer = $row['customer'];
        $sku = $row['sku'];
        $date = $row['log_date'];

        if (!isset($groups[$customer])) {
            $groups[$customer] = [];
        }
        if (!isset($groups[$customer][$sku])) {
            $groups[$customer][$sku] = [];
        }
        if (!in_array($date, $groups[$customer][$sku])) {
            $groups[$customer][$sku][] = $date;
        }
    }

} catch (PDOException $e) {
    // Log error or handle it as needed
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    exit;
}

echo json_encode($groups);
