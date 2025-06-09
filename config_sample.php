<?php
$config = [
    'deviceName' => 'Main NVR',
    'adminUsername' => 'admin',
    'adminPassword' => '', // Will be hashed
    'encryptionKey' => '', // Auto-generated
    'cameras' => [
        [
            'id' => 1,
            'name' => 'Front Door',
            'group' => 'entrance',
            'ip' => '192.168.1.100',
            'username' => 'admin',
            'password' => '', // Encrypted
            'enabled' => true
        ]
    ],
    'retentionDays' => 30,
    'storageLimit' => 100,
    'storageMode' => 'continuous',
    'lastUpdated' => '2024-01-01 00:00:00'
];
?>