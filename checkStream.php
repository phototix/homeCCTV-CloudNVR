<?php
header("Content-Type: application/json");
require_once 'config.php';

$isRunning = false;

if ($config['operatingSystem'] === 'ubuntu') {
    // More specific check for camera streaming processes
    exec('ps aux | grep ffmpeg | grep -v grep', $output, $return_var);
    
    // Check if any of the ffmpeg processes match your expected camera stream command pattern
    foreach ($output as $line) {
        // Adjust this pattern to match your specific ffmpeg command for cameras
        if (strpos($line, 'rtsp://') !== false || strpos($line, 'camera') !== false) {
            $isRunning = true;
            break;
        }
    }
} else {
    // Windows version remains the same
    exec('tasklist /FI "IMAGENAME eq ffmpeg.exe" 2>NUL', $output, $return_var);
    foreach ($output as $line) {
        if (stripos($line, 'ffmpeg.exe') !== false) {
            $isRunning = true;
            break;
        }
    }
}

echo json_encode([
    "streamActive" => $isRunning,
    "processes" => $output  // For debugging - remove in production
]);
?>