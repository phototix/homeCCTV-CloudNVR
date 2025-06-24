<?php
header('Content-Type: application/json');
// Include config file
require_once 'config.php';

// Set paths based on operating system
if ($config['operatingSystem'] === 'ubuntu') {
    // Ubuntu paths
    $cloudDir = '/var/www/html/';  // Typical Apache web root on Ubuntu
    $nvrDir = '/var/www/html/stream/snapshots/';
} else {
    // Default Windows paths
    $cloudDir = 'E:/MAMP/htdocs/';
    $nvrDir = 'E:/MAMP/htdocs/stream/snapshots/';
}

// Storage quotas
$cloudMaxGB = $config['storageLimit'];    // 10GB cloud storage
$nvrMaxGB = 128.00;     // 128GB NVR storage

function getDirectorySize($path) {
    $size = 0;
    
    // Check if directory exists
    if (!is_dir($path)) {
        return ['error' => "Directory not found: $path"];
    }
    
    // Handle potential permission issues
    try {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS)
        );
        
        foreach ($files as $file) {
            if ($file->isFile()) {
                $size += $file->getSize();
            }
        }
    } catch (Exception $e) {
        return ['error' => "Access denied to directory: $path"];
    }
    
    return $size;
}

// Calculate storage usage
$cloudUsedBytes = getDirectorySize($cloudDir);
$nvrUsedBytes = getDirectorySize($nvrDir);

// Handle potential errors
if (is_array($cloudUsedBytes) && isset($cloudUsedBytes['error'])) {
    $cloudUsedGB = 0;
    $cloudError = $cloudUsedBytes['error'];
} else {
    $cloudUsedGB = round($cloudUsedBytes / (1024**3), 3);
    $cloudError = null;
}

if (is_array($nvrUsedBytes) && isset($nvrUsedBytes['error'])) {
    $nvrUsedGB = 0;
    $nvrError = $nvrUsedBytes['error'];
} else {
    $nvrUsedGB = round($nvrUsedBytes / (1024**3), 3);
    $nvrError = null;
}

// Calculate percentages (handle division by zero)
$cloudPercentage = ($cloudMaxGB > 0) ? round(($cloudUsedGB/$cloudMaxGB)*100, 2) : 0;
$nvrPercentage = ($nvrMaxGB > 0) ? round(($nvrUsedGB/$nvrMaxGB)*100, 2) : 0;

// Prepare response
$response = [
    'cloud' => [
        'used' => number_format($cloudUsedGB, 3),
        'max' => number_format($cloudMaxGB, 3),
        'percentage' => $cloudPercentage,
        'free' => number_format($cloudMaxGB - $cloudUsedGB, 3),
        'path' => $cloudDir,
        'error' => $cloudError
    ],
    'nvr' => [
        'used' => number_format($nvrUsedGB, 3),
        'max' => number_format($nvrMaxGB, 3),
        'percentage' => $nvrPercentage,
        'free' => number_format($nvrMaxGB - $nvrUsedGB, 3),
        'path' => $nvrDir,
        'error' => $nvrError
    ],
    'operating_system' => $config['operatingSystem'],
    'updated' => date('H:i:s')
];

echo json_encode($response);
?>