<?php
$imageFolder = 'E:/MAMP/htdocs/stream/snapshots/';
$imageFiles = glob($imageFolder . '*.jpg');
$images = [];

foreach ($imageFiles as $file) {
    $filename = basename($file);
    $nameWithoutExt = pathinfo($filename, PATHINFO_FILENAME);
    $images[] = [
        'thumb' => '/stream/snapshots/' . $filename,
        'full' => '/stream/snapshots/' . $filename,
        'name' => $nameWithoutExt
    ];
}

header('Content-Type: application/json');
echo json_encode($images);
?>