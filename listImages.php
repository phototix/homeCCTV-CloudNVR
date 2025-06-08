<?php
$imageFolder = 'E:/MAMP/htdocs/stream/snapshots/';
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$perPage = 18;
$offset = ($page - 1) * $perPage;

// Get all matching files based on filter
$allFiles = glob($imageFolder . '*.jpg');
$filteredFiles = [];

foreach ($allFiles as $file) {
    $filename = basename($file);
    if ($filter === 'all' || strpos($filename, $filter . '_') === 0) {
        $filteredFiles[] = $file;
    }
}

$totalImages = count($filteredFiles);
$totalPages = ceil($totalImages / $perPage);

// Paginate the filtered results
$paginatedFiles = array_slice($filteredFiles, $offset, $perPage);
$images = [];

foreach ($paginatedFiles as $file) {
    $filename = basename($file);
    $nameWithoutExt = pathinfo($filename, PATHINFO_FILENAME);
    $images[] = [
        'thumb' => 'snapshots/' . $filename,
        'full' => 'snapshots/' . $filename,
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