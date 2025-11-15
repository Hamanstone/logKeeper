<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('HTTP/1.1 403 Forbidden');
    exit;
}

header('Content-Type: application/json');

$logDir = __DIR__ . '/../uploads';
$timeSlots = [];

if (is_dir($logDir)) {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($logDir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::LEAVES_ONLY
    );

    foreach ($iterator as $file) {
        if ($file->isFile()) {
            $modTime = $file->getMTime();
            // Group by 5-minute intervals
            $slot = floor($modTime / 300) * 300;
            if (!isset($timeSlots[$slot])) {
                $timeSlots[$slot] = 0;
            }
            $timeSlots[$slot]++;
        }
    }
}

$labels = [];
$values = [];

// Generate labels and values for the last 24 hours
$endTime = time();
$startTime = $endTime - (24 * 3600);

for ($i = floor($startTime / 300) * 300; $i < $endTime; $i += 300) {
    $labels[] = date('H:i', $i);
    $values[] = $timeSlots[$i] ?? 0;
}

echo json_encode(['labels' => $labels, 'values' => $values]);
