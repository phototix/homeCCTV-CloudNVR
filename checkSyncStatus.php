<?php
header('Content-Type: application/json');

// Simulate sync check (replace with actual logic)
$isSynced = rand(0, 1) === 1; // Randomly true/false for demo

echo json_encode([
    'status' => $isSynced ? 'synced' : 'not synced'
]);
?>