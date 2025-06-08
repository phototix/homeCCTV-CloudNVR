<?php
$imageFolder = 'E:/MAMP/htdocs/stream/snapshots/';
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$perPage = 18;
$offset = ($page - 1) * $perPage;

$imageFiles = glob($imageFolder . '*.jpg');
$totalImages = count($imageFiles);
$totalPages = ceil($totalImages / $perPage);

$paginatedFiles = array_slice($imageFiles, $offset, $perPage);
$images = [];

foreach ($paginatedFiles as $file) {
    $filename = basename($file);
    $nameWithoutExt = pathinfo($filename, PATHINFO_FILENAME);
    $images[] = [
        'thumb' => '/stream/snapshots/' . $filename,
        'full' => '/stream/snapshots/' . $filename,
        'name' => $nameWithoutExt
    ];
}

header('Content-Type: application/json');
echo json_encode([
    'images' => $images,
    'pagination' => [
        'page' => $page,
        'perPage' => $perPage,
        'totalImages' => $totalImages,
        'totalPages' => $totalPages,
        'hasMore' => $page < $totalPages
    ]
]);
?>