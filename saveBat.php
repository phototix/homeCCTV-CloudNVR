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

// Generate the .bat file content
$batContent = '@echo off
setlocal enabledelayedexpansion
:: ===== CONFIGURATION =====
:: FFmpeg path (UPDATE THIS TO YOUR ACTUAL FFMPEG PATH)
set FFMPEG_PATH="E:\MAMP\htdocs\ffmpeg.exe"
:: Camera credentials
set RTSP_USER=' . $config['cameras'][0]['username']. '
set RTSP_PASS=' . decryptPassword($config['cameras'][0]['password'], $config['encryptionKey']) . '
:: Output settings
set STREAM_WIDTH=960
set STREAM_HEIGHT=540
:: ===== PREPARATION =====
:: Get timestamp
for /f "tokens=2 delims==." %%i in (\'"wmic os get localdatetime /value"\') do set dt=%%i
set TIMESTAMP=!dt:~0,4!-!dt:~4,2!-!dt:~6,2!_!dt:~8,2!-!dt:~10,2!-!dt:~12,2!
:: Set folder path
set "SNAPSHOT_DIR=E:\MAMP\htdocs\stream\snapshots"
if not exist "%SNAPSHOT_DIR%" mkdir "%SNAPSHOT_DIR%"
:: ===== 1. INDIVIDUAL SNAPSHOTS =====
echo Capturing individual snapshots...
';

// Add individual camera snapshots for ALL cameras
foreach ($config['cameras'] as $camera) {
    $batContent .= '%FFMPEG_PATH% -loglevel error -rtsp_transport udp -i "rtsp://%RTSP_USER%:%RTSP_PASS%@' . $camera['ip'] . ':554/stream1" -frames:v 1 -y "%SNAPSHOT_DIR%\\' . str_replace(' ', '_', strtolower($camera['name'])) . '_%TIMESTAMP%.jpg"' . "\n";
    $batContent .= 'if errorlevel 1 echo ERROR: Failed to capture ' . $camera['name'] . ' | goto :log_error' . "\n";
}

// Add grid snapshot section with ALL cameras
$batContent .= ':: ===== 2. GRID SNAPSHOT =====
echo Capturing grid snapshot...
%FFMPEG_PATH% -loglevel error ^
  -rtsp_transport udp ^
';

// Add input streams for ALL cameras
foreach ($config['cameras'] as $camera) {
    $batContent .= '  -i "rtsp://%RTSP_USER%:%RTSP_PASS%@' . $camera['ip'] . ':554/stream2" ^' . "\n";
}

// Add blank images if less than 4 cameras
$missingCameras = 4 - count($config['cameras']);
for ($i = 0; $i < $missingCameras; $i++) {
    $batContent .= '  -i E:\MAMP\htdocs\blank.png ^' . "\n";
}

// Add filter complex
$batContent .= '  -filter_complex "';
$filterInputs = [];
for ($i = 0; $i < 4; $i++) {
    $filterInputs[] = '[' . $i . ':v]scale=%STREAM_WIDTH%:%STREAM_HEIGHT%[' . ['tl','tr','bl','br'][$i] . ']';
}
$batContent .= implode(';', $filterInputs) . ';[tl][tr][bl][br]xstack=inputs=4:layout=0_0|w0_0|0_h0|w0_h0" ^
  -frames:v 1 ^
  -y "%SNAPSHOT_DIR%\grid_%TIMESTAMP%.jpg"
if errorlevel 1 echo ERROR: Failed to capture grid | goto :log_error
:: ===== SUCCESS MESSAGE =====
echo Snapshots saved to: %SNAPSHOT_DIR%\
exit /b 0
:log_error
echo Error details logged to %SNAPSHOT_DIR%\snapshot_errors.log
echo [%TIMESTAMP%] Batch Error >> "%SNAPSHOT_DIR%\snapshot_errors.log"
exit /b 1
';

// Write the .bat file
$filename = 'SnapShot.bat';
file_put_contents($filename, $batContent);

echo "Successfully generated batch file: " . $filename . "\n";
header('Location: saveStreamBat.php?status=success');
exit;
?>