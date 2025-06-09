<?php
$configFile = 'config.php';
$sampleFile = 'config_sample.php';

// Create config if missing
if (!file_exists($configFile) {
    copy($sampleFile, $configFile);
}

// Process form data
$settings = [
    'deviceName' => $_POST['deviceName'] ?? 'Main NVR',
    'cameras' => []
];

// Process cameras
foreach ($_POST['cameraName'] as $index => $name) {
    $settings['cameras'][] = [
        'id' => $index + 1,
        'name' => $name,
        'group' => $_POST['cameraGroup'][$index] ?? 'default',
        'enabled' => isset($_POST['cameraEnabled'][$index]) ? true : false
    ];
}

// Add storage/password settings (from previous version)
$settings += [
    'retentionDays' => intval($_POST['retentionDays'] ?? 30),
    'storageLimit' => intval($_POST['storageLimit'] ?? 100),
    'storageMode' => $_POST['storageMode'] ?? 'continuous'
];

if (!empty($_POST['newPassword'])) {
    $settings['passwordHash'] = password_hash($_POST['newPassword'], PASSWORD_DEFAULT);
}

// Save to config.php
$configContent = "<?php\n\$config = " . var_export($settings, true) . ";\n?>";
file_put_contents($configFile, $configContent);

header('Location: settings.html?status=success');
exit;
?>