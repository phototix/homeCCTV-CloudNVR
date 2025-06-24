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

// Generate the .sh file content
$shContent = '#!/bin/bash
# ===== CONFIGURATION =====
# FFmpeg path (UPDATE THIS TO YOUR ACTUAL FFMPEG PATH)
FFMPEG_PATH="/usr/bin/ffmpeg"
# Camera credentials
RTSP_USER="' . $config['cameras'][0]['username'] . '"
RTSP_PASS="' . decryptPassword($config['cameras'][0]['password'], $config['encryptionKey']) . '"
# Output settings
STREAM_WIDTH=960
STREAM_HEIGHT=540
# ===== PREPARATION =====
# Get timestamp
TIMESTAMP=$(date +"%Y-%m-%d_%H-%M-%S")
# Set folder path
SNAPSHOT_DIR="/var/www/sg-cctv/stream/snapshots"
mkdir -p "$SNAPSHOT_DIR"
# ===== 1. INDIVIDUAL SNAPSHOTS =====
echo "Capturing individual snapshots..."
';

// Add individual camera snapshots for ALL cameras
foreach ($config['cameras'] as $camera) {
    $shContent .= '$FFMPEG_PATH -loglevel error -rtsp_transport udp -i "rtsp://$RTSP_USER:$RTSP_PASS@' . $camera['ip'] . ':554/stream1" -frames:v 1 -y "$SNAPSHOT_DIR/' . str_replace(' ', '_', strtolower($camera['name'])) . '_$TIMESTAMP.jpg"' . "\n";
    $shContent .= 'if [ $? -ne 0 ]; then echo "ERROR: Failed to capture ' . $camera['name'] . '" >> "$SNAPSHOT_DIR/snapshot_errors.log"; fi' . "\n";
}

// Add grid snapshot section with ALL cameras
$shContent .= '# ===== 2. GRID SNAPSHOT =====
echo "Capturing grid snapshot..."
$FFMPEG_PATH -loglevel error \
  -rtsp_transport udp \
';

// Add input streams for ALL cameras
foreach ($config['cameras'] as $camera) {
    $shContent .= '  -i "rtsp://$RTSP_USER:$RTSP_PASS@' . $camera['ip'] . ':554/stream2" \\' . "\n";
}

// Add blank images if less than 4 cameras
$missingCameras = 4 - count($config['cameras']);
for ($i = 0; $i < $missingCameras; $i++) {
    $shContent .= '  -i /var/www/sg-cctv/blank.png \\' . "\n";
}

// Add filter complex
$shContent .= '  -filter_complex "';
$filterInputs = [];
for ($i = 0; $i < 4; $i++) {
    $filterInputs[] = '[' . $i . ':v]scale=$STREAM_WIDTH:$STREAM_HEIGHT[' . ['tl','tr','bl','br'][$i] . ']';
}
$shContent .= implode(';', $filterInputs) . ';[tl][tr][bl][br]xstack=inputs=4:layout=0_0|w0_0|0_h0|w0_h0" \
  -frames:v 1 \
  -y "$SNAPSHOT_DIR/grid_$TIMESTAMP.jpg"

if [ $? -ne 0 ]; then
    echo "ERROR: Failed to capture grid" >> "$SNAPSHOT_DIR/snapshot_errors.log"
    echo "[$(date +"%Y-%m-%d_%H-%M-%S")] Batch Error" >> "$SNAPSHOT_DIR/snapshot_errors.log"
    exit 1
fi

# ===== SUCCESS MESSAGE =====
echo "Snapshots saved to: $SNAPSHOT_DIR/"
exit 0
';

// Write the .sh file
$filename = 'snapshot.sh';
file_put_contents($filename, $shContent);
chmod($filename, 0755); // Make the script executable
echo "Successfully generated shell script: " . $filename . "\n";

// Get the current directory path
$currentDir = __DIR__;

// Define the script filename
$scriptName = 'snapshot.sh';
$scriptPath = $currentDir . '/' . $scriptName;

// Verify the script exists
if (!file_exists($scriptPath)) {
    die("Error: $scriptName not found in $currentDir\n");
}

// Make sure the script is executable
if (!is_executable($scriptPath)) {
    chmod($scriptPath, 0755);
}

// Define the cron job entry
$cronJob = "*/5 * * * * " . escapeshellarg($scriptPath);

// Command to add the cron job (without duplicate entries)
$command = "(crontab -l | grep -v -F " . escapeshellarg($scriptPath) . "; echo '$cronJob') | crontab -";

// Execute the command
exec($command, $output, $returnVar);

// Check if the command was successful
if ($returnVar === 0) {
    echo "Successfully added cron job:\n$cronJob\n";
    echo "Script location: $scriptPath\n";
} else {
    echo "Failed to add cron job. Error code: $returnVar\n";
    if (!empty($output)) {
        echo "Output: " . implode("\n", $output) . "\n";
    }
}

header('Location: saveStreamSH.php?status=success');
exit;
?>