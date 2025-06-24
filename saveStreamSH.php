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

// Generate the shell script content
$shellContent = '#!/bin/bash
echo "Starting streams..."
# === CONFIGURATION ===
STREAM_WIDTH=640
STREAM_HEIGHT=360
OUTPUT_WIDTH=1920
OUTPUT_HEIGHT=1080
RTSP_USER="'.$config['adminUsername'].'"
RTSP_PASS="'.decryptPassword($config['cameras'][0]['password'], $config['encryptionKey']).'"
OUTPUT_PATH="/var/www/html/stream/output.m3u8"
# === FFMPEG COMMAND ===
ffmpeg \\
-rtsp_transport udp \\';

// Add input streams for each camera
foreach ($enabledCameras as $index => $camera) {
    $shellContent .= '
-i "rtsp://$RTSP_USER:$RTSP_PASS@'.$camera['ip'].':554/stream2" \\';
}

// Add blank image input and filter complex
$shellContent .= '
-i blank.png \\
-filter_complex "[0:v]scale=$STREAM_WIDTH:$STREAM_HEIGHT[tl];[1:v]scale=$STREAM_WIDTH:$STREAM_HEIGHT[tr];[2:v]scale=$STREAM_WIDTH:$STREAM_HEIGHT[bl];[3:v]scale=$STREAM_WIDTH:$STREAM_HEIGHT[br];[tl][tr][bl][br]xstack=inputs=4:layout=0_0|w0_0|0_h0|w0_h0" \\
-c:v libx264 -preset ultrafast -g 25 \\
-f hls -hls_time 1 -hls_list_size 3 \\
-hls_flags delete_segments+append_list \\
-y "$OUTPUT_PATH" &
# Log the PID of the process
echo $! > stream_pid.txt';

// Write the shell script
file_put_contents('StartCAM-webstream.sh', $shellContent);
// Make the script executable
chmod('StartCAM-webstream.sh', 0755);
echo "Shell script 'StartCAM-webstream.sh' has been generated successfully.";
?>