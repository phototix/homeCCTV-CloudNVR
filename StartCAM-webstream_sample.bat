@echo off
echo Starting streams...\

:: === CONFIGURATION ===
set STREAM_WIDTH=640
set STREAM_HEIGHT=360
set OUTPUT_WIDTH=1920
set OUTPUT_HEIGHT=1080
set RTSP_USER=""
set RTSP_PASS=""
set OUTPUT_PATH="E:\MAMP\htdocs\stream\output.m3u8"

:: === FFMPEG COMMAND ===
ffmpeg.exe ^
  -rtsp_transport udp ^
  -i "rtsp://%RTSP_USER%:%RTSP_PASS%@192.168.1.XXX:554/stream2" ^
  -i "rtsp://%RTSP_USER%:%RTSP_PASS%@192.168.1.XXX:554/stream2" ^
  -i "rtsp://%RTSP_USER%:%RTSP_PASS%@192.168.1.XXX:554/stream2" ^
  -i blank.png ^
  -filter_complex "[0:v]scale=%STREAM_WIDTH%:%STREAM_HEIGHT%[tl];[1:v]scale=%STREAM_WIDTH%:%STREAM_HEIGHT%[tr];[2:v]scale=%STREAM_WIDTH%:%STREAM_HEIGHT%[bl];[3:v]scale=%STREAM_WIDTH%:%STREAM_HEIGHT%[br];[tl][tr][bl][br]xstack=inputs=4:layout=0_0|w0_0|0_h0|w0_h0" ^
  -c:v libx264 -preset ultrafast -g 25 ^
  -f hls -hls_time 1 -hls_list_size 3 ^
  -hls_flags delete_segments+append_list ^
  -y %OUTPUT_PATH%

rem Log the PID of the process
echo %ERRORLEVEL% > stream_pid.txt