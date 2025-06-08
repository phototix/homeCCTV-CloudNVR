@echo off
:: ===== CONFIGURATION =====
:: FFmpeg path (UPDATE THIS TO YOUR ACTUAL FFMPEG PATH)
set FFMPEG_PATH="E:\MAMP\htdocs\ffmpeg.exe"

:: Camera credentials
set RTSP_USER=username
set RTSP_PASS=password

:: Output settings
set SNAPSHOT_DIR=E:\MAMP\htdocs\stream\snapshots
set STREAM_WIDTH=960
set STREAM_HEIGHT=540

:: ===== PREPARATION =====
:: Create timestamp (format: YYYY-MM-DD_HH-MM-SS)
for /f "tokens=1-3 delims=- " %%a in ("%date%") do set DATE_STR=%%a-%%c-%%b
for /f "tokens=1-3 delims=:." %%a in ("%time%") do set TIME_STR=%%a-%%b-%%c
set TIMESTAMP=%DATE_STR%_%TIME_STR%

:: Create directory if missing
if not exist "%SNAPSHOT_DIR%" mkdir "%SNAPSHOT_DIR%"

:: ===== 1. INDIVIDUAL SNAPSHOTS =====
echo Capturing individual snapshots...
%FFMPEG_PATH% -loglevel error -rtsp_transport udp -i "rtsp://%RTSP_USER%:%RTSP_PASS%@192.168.1.XXX:554/stream1" -frames:v 1 -y "%SNAPSHOT_DIR%\cam1_%TIMESTAMP%.jpg"
if errorlevel 1 echo ERROR: Failed to capture cam1 | goto :log_error

%FFMPEG_PATH% -loglevel error -rtsp_transport udp -i "rtsp://%RTSP_USER%:%RTSP_PASS%@192.168.1.XXX:554/stream1" -frames:v 1 -y "%SNAPSHOT_DIR%\cam2_%TIMESTAMP%.jpg"
if errorlevel 1 echo ERROR: Failed to capture cam2 | goto :log_error

%FFMPEG_PATH% -loglevel error -rtsp_transport udp -i "rtsp://%RTSP_USER%:%RTSP_PASS%@192.168.1.XXX:554/stream1" -frames:v 1 -y "%SNAPSHOT_DIR%\cam3_%TIMESTAMP%.jpg"
if errorlevel 1 echo ERROR: Failed to capture cam3 | goto :log_error

:: ===== 2. 2x2 GRID SNAPSHOT =====
echo Capturing 2x2 grid snapshot...
%FFMPEG_PATH% -loglevel error ^
  -rtsp_transport udp ^
  -i "rtsp://%RTSP_USER%:%RTSP_PASS%@192.168.1.XXX:554/stream2" ^
  -i "rtsp://%RTSP_USER%:%RTSP_PASS%@192.168.1.XXX:554/stream2" ^
  -i "rtsp://%RTSP_USER%:%RTSP_PASS%@192.168.1.XXX:554/stream2" ^
  -i E:\MAMP\htdocs\blank.png ^
  -filter_complex "[0:v]scale=%STREAM_WIDTH%:%STREAM_HEIGHT%[tl];[1:v]scale=%STREAM_WIDTH%:%STREAM_HEIGHT%[tr];[2:v]scale=%STREAM_WIDTH%:%STREAM_HEIGHT%[bl];[3:v]scale=%STREAM_WIDTH%:%STREAM_HEIGHT%[br];[tl][tr][bl][br]xstack=inputs=4:layout=0_0|w0_0|0_h0|w0_h0" ^
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