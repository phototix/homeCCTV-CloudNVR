<?php
// Function to decrypt password (assuming base64 encoding)
function decryptPassword($encrypted, $key) {
    $data = base64_decode($encrypted);
    $iv = substr($data, 0, openssl_cipher_iv_length('aes-256-cbc'));
    $ciphertext = substr($data, openssl_cipher_iv_length('aes-256-cbc'));
    return openssl_decrypt($ciphertext, 'aes-256-cbc', $key, 0, $iv);
}

// Load config if exists
$config = [];
if (file_exists('config.php')) {
    include 'config.php';
}

// Check if config was loaded properly
if (empty($config) || !isset($config['cameras'])) {
    die("Error: Configuration not loaded or invalid. Please check config.php");
}

// Extract enabled cameras
$enabledCameras = array_filter($config['cameras'], function($camera) {
    return $camera['enabled'];
});

// Generate the batch file content
$batchContent = '@echo off
echo Starting streams...\

:: === CONFIGURATION ===
set STREAM_WIDTH=640
set STREAM_HEIGHT=360
set OUTPUT_WIDTH=1920
set OUTPUT_HEIGHT=1080
set RTSP_USER="'.$config['adminUsername'].'"
set RTSP_PASS="'.decryptPassword($config['cameras'][0]['password'], $config['encryptionKey']).'"
set OUTPUT_PATH="E:\MAMP\htdocs\stream\output.m3u8"

:: === FFMPEG COMMAND ===
ffmpeg.exe ^';

// Add input streams for each camera
foreach ($enabledCameras as $index => $camera) {
    $batchContent .= '
  -rtsp_transport udp ^
  -i "rtsp://%RTSP_USER%:%RTSP_PASS%@'.$camera['ip'].':554/stream2" ^';
}

// Add blank image input and filter complex
$batchContent .= '
  -i blank.png ^
  -filter_complex "';

// Add scaling for each camera
foreach ($enabledCameras as $index => $camera) {
    $batchContent .= '['.$index.':v]scale=%STREAM_WIDTH%:%STREAM_HEIGHT%['.strtolower(substr($camera['group'], 0, 1)).substr($camera['name'], -1).'];';
}

// Add the xstack layout
$batchContent .= '[tl][tr][bl][br]xstack=inputs=4:layout=0_0|w0_0|0_h0|w0_h0" ^
  -c:v libx264 -preset ultrafast -g 25 ^
  -f hls -hls_time 1 -hls_list_size 3 ^
  -hls_flags delete_segments+append_list ^
  -y %OUTPUT_PATH%

rem Log the PID of the process
echo %ERRORLEVEL% > stream_pid.txt';

// Write the batch file
file_put_contents('StartCAM-webstream.bat', $batchContent);

echo "Batch file 'StartCAM-webstream.bat' & 'SnapShot.bat' has been generated successfully.";
?>