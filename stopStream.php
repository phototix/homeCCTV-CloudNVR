<?php
// Secure the script to prevent unauthorized access
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    exit("Unauthorized access");
}

// Path to the PID file
$pidFile = "stream_pid.txt";

// Check if the PID file exists
if (!file_exists($pidFile)) {
    exit("Stream is not running.");
}

// Get the PID
$pid = file_get_contents($pidFile);
$pid = trim($pid);

// Kill the process
exec('taskkill /F /IM ffmpeg.exe', $output, $return_var);

// Path to the directory containing .ts and .m3u8 files
$streamDir = "E:\\MAMP\\htdocs\\stream";

// Cleanup .ts and .m3u8 files if the directory exists
if (is_dir($streamDir)) {
    // Delete .ts files
    array_map('unlink', glob("$streamDir/*.ts"));
    // Delete .m3u8 files
    array_map('unlink', glob("$streamDir/*.m3u8"));
}

// Check execution result
if ($return_var === 0) {
    echo "Stream stopped successfully.";
} else {
    echo "Failed to stop stream. Error code: $return_var";
}
?>