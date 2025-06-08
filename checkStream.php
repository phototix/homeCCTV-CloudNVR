<?php
header("Content-Type: application/json");

// Check if ffmpeg.exe is running
exec('tasklist /FI "IMAGENAME eq ffmpeg.exe" 2>NUL', $output, $return_var);

// Look for "ffmpeg.exe" in the output
$isRunning = false;
foreach ($output as $line) {
    if (stripos($line, 'ffmpeg.exe') !== false) {
        $isRunning = true;
        break;
    }
}

// Return JSON response
echo json_encode([
    "streamActive" => $isRunning
]);
?>
