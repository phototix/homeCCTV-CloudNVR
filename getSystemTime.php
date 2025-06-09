<?php
header('Content-Type: application/json');
date_default_timezone_set('Asia/Singapore'); // Set your timezone

echo json_encode([
    'time' => date('H:i:s'),
    'date' => date('d M Y')
]);
?>