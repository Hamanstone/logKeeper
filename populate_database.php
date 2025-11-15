<?php
require_once __DIR__ . '/config.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Truncate the table to avoid duplicates on re-runs
    $pdo->exec("TRUNCATE TABLE log_files");
    echo "Table 'log_files' truncated.\n";

    $logDir = __DIR__ . '/uploads';

    if (!is_dir($logDir)) {
        echo "Uploads directory not found.\n";
        exit;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($logDir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::LEAVES_ONLY
    );

    $stmt = $pdo->prepare(
        "INSERT INTO log_files (customer, sku, log_date, file_name, file_path, file_size, modification_time) " .
        "VALUES (:customer, :sku, :log_date, :file_name, :file_path, :file_size, :modification_time)"
    );

    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getFilename() !== '.DS_Store' && $file->getFilename() !== '.gitkeep') {
            $filePath = $file->getPathname();
            $relativePath = str_replace($logDir . DIRECTORY_SEPARATOR, '', $filePath);
            $parts = explode(DIRECTORY_SEPARATOR, $relativePath);

            if (count($parts) === 4) { // customer/sku/date/filename.log
                list($customer, $sku, $dateStr, $fileName) = $parts;

                try {
                    $log_date = new DateTime($dateStr);
                    $modification_time = (new DateTime())->setTimestamp($file->getMTime());

                    $stmt->execute([
                        ':customer' => $customer,
                        ':sku' => $sku,
                        ':log_date' => $log_date->format('Y-m-d'),
                        ':file_name' => $fileName,
                        ':file_path' => $filePath,
                        ':file_size' => $file->getSize(),
                        ':modification_time' => $modification_time->format('Y-m-d H:i:s')
                    ]);
                    echo "Inserted: $filePath\n";
                } catch (Exception $e) {
                    echo "Skipping invalid date format: $dateStr in path $filePath\n";
                }
            }
        }
    }

    echo "Database population complete.\n";

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage() . "\n");
}

