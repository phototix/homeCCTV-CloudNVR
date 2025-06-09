<?php
header('Content-Type: application/json');

// Define the directory to check
$directory = 'E:/MAMP/htdocs/';
$maxAllowedGB = 10; // User's storage limit in GB

// Check if directory exists
if (!is_dir($directory)) {
    echo json_encode([
        'error' => 'Directory not found: ' . $directory
    ]);
    exit;
}

// Calculate used space (in bytes)
$usedBytes = 0;
$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($directory)
);
foreach ($files as $file) {
    if ($file->isFile()) {
        $usedBytes += $file->getSize();
    }
}

// Convert to GB
$usedGB = round($usedBytes / (1024 * 1024 * 1024), 2);
$maxAllowedBytes = $maxAllowedGB * 1024 * 1024 * 1024;
$percentageUsed = round(($usedBytes / $maxAllowedBytes) * 100, 2);

// Return JSON response
echo json_encode([
    'used_gb' => $usedGB,
    'max_gb' => $maxAllowedGB,
    'percentage_used' => $percentageUsed,
    'used_bytes' => $usedBytes,
    'max_bytes' => $maxAllowedBytes,
    'directory' => $directory
]);
?>