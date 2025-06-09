<?php
// Encryption functions
function encryptPassword($plaintext, $key) {
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
    $ciphertext = openssl_encrypt($plaintext, 'aes-256-cbc', $key, 0, $iv);
    return base64_encode($iv . $ciphertext);
}

function decryptPassword($encrypted, $key) {
    $data = base64_decode($encrypted);
    $iv = substr($data, 0, openssl_cipher_iv_length('aes-256-cbc'));
    $ciphertext = substr($data, openssl_cipher_iv_length('aes-256-cbc'));
    return openssl_decrypt($ciphertext, 'aes-256-cbc', $key, 0, $iv);
}

// Load existing config
$config = [];
if (file_exists('config.php')) {
    include 'config.php';
}

// Generate encryption key if not exists
if (!isset($config['encryptionKey'])) {
    $config['encryptionKey'] = base64_encode(openssl_random_pseudo_bytes(32));
}

// Process form data
$config['deviceName'] = $_POST['deviceName'] ?? 'Main NVR';
$config['adminUsername'] = $_POST['adminUsername'] ?? 'admin';

// Update admin password if provided
if (!empty($_POST['adminPassword'])) {
    $config['adminPassword'] = password_hash($_POST['adminPassword'], PASSWORD_DEFAULT);
}

// Process cameras
$config['cameras'] = [];
foreach ($_POST['cameraName'] as $index => $name) {
    $camera = [
        'id' => $index + 1,
        'name' => $name,
        'group' => $_POST['cameraGroup'][$index] ?? 'default',
        'ip' => $_POST['cameraIP'][$index],
        'username' => $_POST['cameraUser'][$index] ?? 'admin',
        'enabled' => isset($_POST['cameraEnabled'][$index])
    ];
    
    // Handle password (keep existing if not changed)
    $password = $_POST['cameraPassword'][$index] ?? '';
    if ($password !== '********' && !empty($password)) {
        $camera['password'] = encryptPassword($password, base64_decode($config['encryptionKey']));
    } elseif (isset($config['cameras'][$index]['password'])) {
        $camera['password'] = $config['cameras'][$index]['password'];
    }
    
    $config['cameras'][] = $camera;
}

// Save other settings
$config['retentionDays'] = intval($_POST['retentionDays'] ?? 30);
$config['storageLimit'] = intval($_POST['storageLimit'] ?? 100);
$config['storageMode'] = $_POST['storageMode'] ?? 'continuous';
$config['lastUpdated'] = date('Y-m-d H:i:s');

// Save to config.php
$configContent = "<?php\n\$config = " . var_export($config, true) . ";\n?>";
file_put_contents('config.php', $configContent);

header('Location: settings.php?status=success');
exit;
?>