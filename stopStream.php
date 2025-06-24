<?php
// Include config file
require_once 'config.php';
// Secure the script to prevent unauthorized access
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    exit("Unauthorized access");
}

// Kill all ffmpeg processes based on operating system
if ($config['operatingSystem'] === 'ubuntu') {
    // Linux/Ubuntu command to kill all ffmpeg processes
    exec("pkill -9 ffmpeg", $output, $return_var);
} else {
    // Windows command
    exec('taskkill /F /IM ffmpeg.exe', $output, $return_var);
}

// Path to the directory containing .ts and .m3u8 files
$streamDir = "E:\\MAMP\\htdocs\\stream";
// For Ubuntu, adjust the path if needed
if ($config['operatingSystem'] === 'ubuntu') {
    $streamDir = "/var/www/sg-cctv/stream"; // Typical Ubuntu web directory
}

// Cleanup .ts and .m3u8 files if the directory exists
if (is_dir($streamDir)) {
    // Delete .ts files
    array_map('unlink', glob("$streamDir/*.ts"));
    // Delete .m3u8 files
    array_map('unlink', glob("$streamDir/*.m3u8"));
}

// Check execution result
if ($return_var === 0 || $return_var === 1) {
    // Return code 0 means processes were found and killed
    // Return code 1 means no processes were found (which is also acceptable)
    echo "Stream stopped successfully.";
} else {
    echo "Failed to stop stream. Error code: $return_var";
}
?>