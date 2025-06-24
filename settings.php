<?php
// Load config if exists
$config = [];
if (file_exists('config.php')) {
    include 'config.php';
}
function decryptPassword($encrypted, $key) {
    $data = base64_decode($encrypted);
    $iv = substr($data, 0, openssl_cipher_iv_length('aes-256-cbc'));
    $ciphertext = substr($data, openssl_cipher_iv_length('aes-256-cbc'));
    return openssl_decrypt($ciphertext, 'aes-256-cbc', $key, 0, $iv);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CloudNVR - Settings</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
  <style>
    .tooltip-icon { cursor: help; color: #0d6efd; }
    .camera-item { border-left: 4px solid #0d6efd; }
    .camera-credentials { background-color: #f8f9fa; border-radius: 5px; }
    .password-toggle { cursor: pointer; }
  </style>
</head>
<body>
  <div class="container py-4">
    <h1 class="mb-4"><i class="bi bi-gear"></i> CloudNVR Settings</h1>
    
    <form id="settingsForm" action="saveSettings.php" method="POST">
      
      <!-- Device Name -->
      <div class="card mb-4">
        <div class="card-header bg-dark text-white">
          <h5><i class="bi bi-pc-display"></i> NVR Device</h5>
        </div>
        <div class="card-body">
          <div class="mb-3">
            <label for="deviceName" class="form-label">Device Name</label>
            <input type="text" class="form-control" id="deviceName" name="deviceName"
                   value="<?php echo htmlspecialchars($config['deviceName'] ?? 'Main NVR') ?>" required>
          </div>
          
          <!-- Operating System Selection -->
          <div class="mb-3">
            <label for="operatingSystem" class="form-label">Operating System</label>
            <select class="form-select" id="operatingSystem" name="operatingSystem" required>
              <option value="windows" <?php echo (isset($config['operatingSystem']) && $config['operatingSystem'] === 'windows' ? 'selected' : ''); ?>>Windows</option>
              <option value="ubuntu" <?php echo (isset($config['operatingSystem']) && $config['operatingSystem'] === 'ubuntu' ? 'selected' : ''); ?>>Ubuntu</option>
              <option value="others" <?php echo (isset($config['operatingSystem']) && $config['operatingSystem'] === 'others' ? 'selected' : ''); ?>>Other OS</option>
            </select>
            <div class="form-text">Select the operating system where NVR is running</div>
          </div>
        </div>
      </div>

      <!-- Camera Management -->
      <div class="card mb-4">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
          <h5><i class="bi bi-camera-video"></i> Camera Management</h5>
          <button type="button" class="btn btn-sm btn-light" id="addCameraBtn">
            <i class="bi bi-plus-circle"></i> Add Camera
          </button>
        </div>
        <div class="card-body">
          <div id="cameraList" class="list-group">
            <?php foreach ($config['cameras'] ?? [] as $index => $camera): ?>
            <div class="list-group-item camera-item mb-3" data-camera-id="<?php echo $index + 1 ?>">
              <div class="d-flex justify-content-between align-items-start">
                <div class="flex-grow-1 me-3">
                  <div class="row g-2 mb-2">
                    <div class="col-md-4">
                      <label class="form-label">Camera Name</label>
                      <input type="text" class="form-control" name="cameraName[]" 
                             value="<?php echo htmlspecialchars($camera['name']) ?>" required>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">Group</label>
                      <select class="form-select" name="cameraGroup[]">
                        <option value="entrance" <?php echo ($camera['group'] == 'entrance') ? 'selected' : '' ?>>Entrance</option>
                        <option value="parking" <?php echo ($camera['group'] == 'parking') ? 'selected' : '' ?>>Parking Lot</option>
                        <option value="indoor" <?php echo ($camera['group'] == 'indoor') ? 'selected' : '' ?>>Indoor</option>
                      </select>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">IP Address</label>
                      <input type="text" class="form-control" name="cameraIP[]" 
                             value="<?php echo htmlspecialchars($camera['ip'] ?? '') ?>" required>
                    </div>
                  </div>
                  
                  <div class="row g-2 camera-credentials p-3 mb-2">
                    <div class="col-md-6">
                      <label class="form-label">Username</label>
                      <input type="text" class="form-control" name="cameraUser[]" 
                             value="<?php echo htmlspecialchars($camera['username'] ?? 'admin') ?>">
                    </div>
                    <div class="col-md-6">
                      <label class="form-label">Password:</label>
                      <div class="input-group">
                        <input type="password" class="form-control camera-password" 
                               name="cameraPassword[]" value="<?php echo decryptPassword($camera['password'], $config['encryptionKey']) ?>">
                        <button class="btn btn-outline-secondary password-toggle" type="button">
                          <i class="bi bi-eye"></i>
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="d-flex flex-column">
                  <div class="form-check form-switch mb-2">
                    <input class="form-check-input" type="checkbox" name="cameraEnabled[]" 
                          <?php echo ($camera['enabled'] ?? true) ? 'checked' : '' ?>>
                  </div>
                  <button type="button" class="btn btn-sm btn-danger delete-camera">
                    <i class="bi bi-trash"></i>
                  </button>
                </div>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <!-- Storage Configuration Section -->
      <div class="card mb-4">
        <div class="card-header bg-success text-white">
          <h5><i class="bi bi-hdd"></i> Storage Configuration</h5>
        </div>
        <div class="card-body">
          <div class="mb-3">
            <label for="retentionDays" class="form-label">
              Retention Period (Days)
              <i class="bi bi-info-circle tooltip-icon" data-bs-toggle="tooltip" title="How long to keep recordings (1-365 days)"></i>
            </label>
            <input type="number" class="form-control" id="retentionDays" name="retentionDays" 
                   min="1" max="365" 
                   value="<?php echo isset($config['retentionDays']) ? htmlspecialchars($config['retentionDays']) : '30' ?>" required>
          </div>
          <div class="mb-3">
            <label for="storageLimit" class="form-label">
              Storage Limit (GB)
              <i class="bi bi-info-circle tooltip-icon" data-bs-toggle="tooltip" title="Maximum storage to use (system will overwrite oldest files)"></i>
            </label>
            <input type="number" class="form-control" id="storageLimit" name="storageLimit" 
                   min="1" 
                   value="<?php echo isset($config['storageLimit']) ? htmlspecialchars($config['storageLimit']) : '100' ?>" required>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="radio" name="storageMode" id="continuousMode" value="continuous" 
                  <?php echo (!isset($config['storageMode']) || $config['storageMode'] === 'continuous') ? 'checked' : '' ?>>
            <label class="form-check-label" for="continuousMode">Continuous Recording</label>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="radio" name="storageMode" id="motionMode" value="motion" 
                  <?php echo (isset($config['storageMode']) && $config['storageMode'] === 'motion') ? 'checked' : '' ?>>
            <label class="form-check-label" for="motionMode">Motion-Activated Only</label>
          </div>
          
          <!-- Storage Status (Read-only) -->
          <?php if (isset($config['storageUsage'])): ?>
          <div class="mt-4">
            <h6>Current Storage Status</h6>
            <div class="progress mb-2" style="height: 25px;">
              <div class="progress-bar bg-info" role="progressbar" 
                   style="width: <?php echo min(100, ($config['storageUsage']['usedGB'] / $config['storageLimit']) * 100) ?>%" 
                   aria-valuenow="<?php echo $config['storageUsage']['usedGB'] ?>" 
                   aria-valuemin="0" 
                   aria-valuemax="<?php echo $config['storageLimit'] ?>">
                <?php echo round(($config['storageUsage']['usedGB'] / $config['storageLimit']) * 100, 1) ?>%
              </div>
            </div>
            <small class="text-muted">
              <?php echo $config['storageUsage']['usedGB'] ?> GB used of <?php echo $config['storageLimit'] ?> GB
              (<?php echo $config['storageUsage']['daysRemaining'] ?> days remaining)
            </small>
          </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- User Authentication -->
      <div class="card mb-4">
        <div class="card-header bg-warning text-dark">
          <h5><i class="bi bi-shield-lock"></i> Admin Authentication</h5>
        </div>
        <div class="card-body">
          <div class="mb-3">
            <label for="adminUsername" class="form-label">Username</label>
            <input type="text" class="form-control" id="adminUsername" name="adminUsername" 
                   value="<?php echo htmlspecialchars($config['adminUsername'] ?? 'admin') ?>" required>
          </div>
          <div class="mb-3">
            <label for="adminPassword" class="form-label">New Password</label>
            <input type="password" class="form-control" id="adminPassword" name="adminPassword">
            <div class="form-text">Leave blank to keep current password</div>
          </div>
        </div>
      </div>

      <div class="d-grid gap-2">
        <button type="submit" class="btn btn-primary btn-lg">
          <i class="bi bi-save"></i> Save Settings
        </button>
      </div>
    </form>
  </div>

  <!-- JavaScript -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Password visibility toggle
      document.addEventListener('click', function(e) {
        if (e.target.closest('.password-toggle')) {
          const toggleBtn = e.target.closest('.password-toggle');
          const passwordInput = toggleBtn.previousElementSibling;
          const isPassword = passwordInput.type === 'password';
          
          passwordInput.type = isPassword ? 'text' : 'password';
          toggleBtn.innerHTML = isPassword ? '<i class="bi bi-eye-slash"></i>' : '<i class="bi bi-eye"></i>';
          
          // Reset placeholder if showing actual password
          if (passwordInput.value === '********') {
            passwordInput.value = '';
          }
        }
        
        // Delete camera
        if (e.target.closest('.delete-camera')) {
          e.target.closest('.camera-item').remove();
        }
      });

      // Add new camera
      document.getElementById('addCameraBtn').addEventListener('click', function() {
        const cameraList = document.getElementById('cameraList');
        const newId = cameraList.children.length + 1;
        
        const newCamera = document.createElement('div');
        newCamera.className = 'list-group-item camera-item mb-3';
        newCamera.dataset.cameraId = newId;
        newCamera.innerHTML = `
          <div class="d-flex justify-content-between align-items-start">
            <div class="flex-grow-1 me-3">
              <div class="row g-2 mb-2">
                <div class="col-md-4">
                  <label class="form-label">Camera Name</label>
                  <input type="text" class="form-control" name="cameraName[]" required>
                </div>
                <div class="col-md-4">
                  <label class="form-label">Group</label>
                  <select class="form-select" name="cameraGroup[]">
                    <option value="entrance">Entrance</option>
                    <option value="parking">Parking Lot</option>
                    <option value="indoor">Indoor</option>
                  </select>
                </div>
                <div class="col-md-4">
                  <label class="form-label">IP Address</label>
                  <input type="text" class="form-control" name="cameraIP[]" required>
                </div>
              </div>
              
              <div class="row g-2 camera-credentials p-3 mb-2">
                <div class="col-md-6">
                  <label class="form-label">Username</label>
                  <input type="text" class="form-control" name="cameraUser[]" placeholder="admin">
                </div>
                <div class="col-md-6">
                  <label class="form-label">Password</label>
                  <div class="input-group">
                    <input type="password" class="form-control camera-password" name="cameraPassword[]">
                    <button class="btn btn-outline-secondary password-toggle" type="button">
                      <i class="bi bi-eye"></i>
                    </button>
                  </div>
                </div>
              </div>
            </div>
            <div class="d-flex flex-column">
              <div class="form-check form-switch mb-2">
                <input class="form-check-input" type="checkbox" name="cameraEnabled[]" checked>
              </div>
              <button type="button" class="btn btn-sm btn-danger delete-camera">
                <i class="bi bi-trash"></i>
              </button>
            </div>
          </div>`;
        
        cameraList.appendChild(newCamera);
      });

      // IP validation
      document.getElementById('settingsForm').addEventListener('submit', function(e) {
        const ipInputs = document.querySelectorAll('input[name="cameraIP[]"]');
        
        ipInputs.forEach(input => {
          if (!/^(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/.test(input.value)) {
            e.preventDefault();
            input.focus();
            alert('Please enter a valid IP address for ' + (input.closest('.camera-item').querySelector('input[name="cameraName[]"]').value || 'the camera'));
            return false;
          }
        });
      });
    });
  </script>
</body>
</html>