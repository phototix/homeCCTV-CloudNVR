# CloudNVR - Cloud-Based Network Video Recorder

![Dashboard](screenshots/dashboard.png)

CloudNVR is a lightweight, PHP-based network video recorder solution that allows you to monitor IP cameras, view live feeds, capture snapshots, and manage recordings.

## Features

- Live video streaming from multiple IP cameras
- Scheduled snapshot capture
- Centralized dashboard for monitoring
- Configurable recording settings
- Lightweight PHP backend with FFMPEG integration

## Screenshots

### Live Video Feed
![Video Feed](screenshots/video.png)

### Captured Snapshots
![Snapshots](screenshots/snapshots.png)

### Configuration Settings
![Settings](screenshots/settings.png)

## Prerequisites

- PHP 7.4 or higher
- FFMPEG installed and added to system PATH
- Web server (Apache/Nginx)
- IP cameras with RTSP/HTTP streams
- Task Scheduler (for automated snapshots)

## Installation

1. Clone this repository to your web server:
   ```bash
   git clone https://github.com/yourusername/CloudNVR.git
   ```

2. Rename configuration file:
   ```bash
   cd CloudNVR
   mv config_sample.php config.php
   ```

3. Edit `config.php` with your camera details and system settings:
   ```php
   // Example camera configuration
   $cameras = [
       [
           'name' => 'Front Door',
           'rtsp_url' => 'rtsp://username:password@camera-ip:554/stream',
           'thumbnail' => 'front_door.jpg'
       ]
   ];
   ```

4. For scheduled snapshots:
   - Rename `SnapShot_sample.bat` to `SnapShot.bat`
   - Edit the batch file with your desired parameters
   - Set up a Task Scheduler job to run the batch file at your preferred interval

## Usage

1. Access the web interface at `http://your-server/CloudNVR/`
2. Dashboard shows all configured cameras
3. Click on any camera to view live feed
4. Use the controls to:
   - Take manual snapshots
   - Start/stop recording
   - Adjust camera settings

## Configuration

Key configuration options in `config.php`:

- `$storage_path` - Where to save recordings and snapshots
- `$ffmpeg_path` - Path to FFMPEG executable
- `$max_storage` - Maximum storage allocation (in GB)
- `$retention_days` - How long to keep recordings
- Camera array configuration

## Scheduled Snapshots

To set up automatic snapshots:

1. Edit `SnapShot.bat` with your desired interval and parameters
2. Open Task Scheduler
3. Create a new task:
   - Trigger: Set your desired schedule
   - Action: Start `SnapShot.bat`
   - Run with highest privileges

## Troubleshooting

- **No video feed**: Check camera URLs and network connectivity
- **FFMPEG errors**: Verify FFMPEG is installed and in system PATH
- **Permission issues**: Ensure PHP has write access to storage directory
- **High CPU usage**: Adjust FFMPEG parameters for lower resolution/bitrate

## Contributing

Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.