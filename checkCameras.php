<?php
header('Content-Type: application/json');
// Include config file
require_once 'config.php';

// Function to check camera status (ping + port check)
function checkCameraStatus($ip, $os) {
    // Ping check (OS specific)
    if ($os === 'windows') {
        $ping = exec("ping -n 1 $ip", $output, $status);
    } else {
        // Linux/Ubuntu ping command (-c for count, -W for timeout)
        $ping = exec("ping -c 1 -W 1 $ip", $output, $status);
    }
    
    if ($status === 0) {
        // Optional: Check RTSP port (554)
        $rtspCheck = @fsockopen($ip, 554, $errno, $errstr, 1);
        if ($rtspCheck) {
            fclose($rtspCheck);
            return 'online';
        }
        return 'online'; // Basic ping success
    }
    return 'offline';
}

// Process cameras
$results = [
    'total' => 0,
    'online' => 0,
    'offline' => 0,
    'cameras' => []
];

foreach ($config['cameras'] as $camera) {
    if (!$camera['enabled']) continue;
    
    $status = checkCameraStatus($camera['ip'], $config['operatingSystem']);
    
    $results['cameras'][] = [
        'id' => $camera['id'],
        'name' => $camera['name'],
        'ip' => $camera['ip'],
        'group' => $camera['group'],
        'status' => $status
    ];
    
    $results['total']++;
    ($status === 'online') ? $results['online']++ : $results['offline']++;
}

$results['last_checked'] = date('Y-m-d H:i:s');
$results['operating_system'] = $config['operatingSystem'];
echo json_encode($results);
?>