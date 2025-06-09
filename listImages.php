<?php
$directory = 'E:/MAMP/htdocs/stream/snapshots/';
$perPage = 18;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$filterDate = isset($_GET['date']) ? $_GET['date'] : '';
$filterCamera = isset($_GET['camera']) ? $_GET['camera'] : 'all';

// Validate and sanitize inputs
if ($page < 1) $page = 1;
$filterDate = preg_match('/^\d{4}-\d{2}-\d{2}$/', $filterDate) ? $filterDate : '';
$filterCamera = in_array($filterCamera, ['all', 'cam1', 'cam2', 'cam3', 'grid']) ? $filterCamera : 'all';

// Get all image files
$images = [];
if (is_dir($directory)) {
    $files = scandir($directory);
    foreach ($files as $file) {
        if (preg_match('/^(cam1|cam2|cam3|grid)_(\d{4}-\d{2}-\d{2})_(\d{2})-(\d{2})-(\d{2})\.jpg$/i', $file, $matches)) {
            $camera = strtolower($matches[1]);
            $date = $matches[2];
            $time = $matches[3] . ':' . $matches[4] . ':' . $matches[5];
            
            // Apply filters
            if (($filterCamera === 'all' || $camera === $filterCamera) && 
                (empty($filterDate) || $date === $filterDate)) {
                $images[] = [
                    'filename' => $file,
                    'path' => $directory . $file,
                    'camera' => $camera,
                    'date' => $date,
                    'time' => $time,
                    'timestamp' => strtotime($date . ' ' . $time)
                ];
            }
        }
    }
}

// Sort by timestamp (newest first)
usort($images, function($a, $b) {
    return $b['timestamp'] - $a['timestamp'];
});

// Pagination
$totalImages = count($images);
$totalPages = ceil($totalImages / $perPage);
$offset = ($page - 1) * $perPage;
$paginatedImages = array_slice($images, $offset, $perPage);

// Output HTML
foreach ($paginatedImages as $image) {
    $thumbnailPath = '/stream/snapshots/' . $image['filename'];
    $fullPath = '/stream/snapshots/' . $image['filename'];
    $cameraName = ucfirst($image['camera']);
    $dateTime = date('Y-m-d H:i:s', $image['timestamp']);
    
    echo <<<HTML
    <div class="col-md-4 col-sm-6 image-container">
        <div class="card">
            <a href="$fullPath" data-lightbox="gallery" data-title="$cameraName - $dateTime">
                <img src="$thumbnailPath" class="image-thumbnail card-img-top" alt="$cameraName - $dateTime">
            </a>
            <div class="card-body">
                <small class="text-muted">$cameraName - $dateTime</small>
            </div>
        </div>
    </div>
HTML;
}
?>