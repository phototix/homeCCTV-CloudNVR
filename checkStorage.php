<?php
header('Content-Type: application/json');

// Cloud Storage (User's 10GB Quota)
$cloudDir = 'E:/MAMP/htdocs/';
$cloudMaxGB = 10.00; // 10GB with decimal precision

// NVR Local Storage (Snapshots + 128GB Disk)
$nvrDir = 'E:/MAMP/htdocs/stream/snapshots/';
$nvrMaxGB = 128.00; // 128GB with decimal precision

function getDirectorySize($path) {
    $size = 0;
    if (!is_dir($path)) return ['error' => "Directory not found: $path"];
    
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS)
    );
    
    foreach ($files as $file) {
        if ($file->isFile()) $size += $file->getSize();
    }
    return $size;
}

// Calculate with precise decimals
$cloudUsedBytes = getDirectorySize($cloudDir);
$cloudUsedGB = round($cloudUsedBytes / (1024**3), 3); // 3 decimal places
$cloudPercentage = round(($cloudUsedGB/$cloudMaxGB)*100, 2);

$nvrUsedBytes = getDirectorySize($nvrDir);
$nvrUsedGB = round($nvrUsedBytes / (1024**3), 3); // 3 decimal places
$nvrPercentage = round(($nvrUsedGB/$nvrMaxGB)*100, 2);

echo json_encode([
    'cloud' => [
        'used' => number_format($cloudUsedGB, 3),
        'max' => number_format($cloudMaxGB, 3),
        'percentage' => $cloudPercentage,
        'free' => number_format($cloudMaxGB - $cloudUsedGB, 3)
    ],
    'nvr' => [
        'used' => number_format($nvrUsedGB, 3),
        'max' => number_format($nvrMaxGB, 3),
        'percentage' => $nvrPercentage,
        'free' => number_format($nvrMaxGB - $nvrUsedGB, 3)
    ],
    'updated' => date('H:i:s')
]);
?>