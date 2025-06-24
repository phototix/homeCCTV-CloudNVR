<?php
// Include config file
require_once 'config.php';
// Secure the script to prevent unauthorized access
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    exit("Unauthorized access");
}

if ($config['operatingSystem'] === 'ubuntu') {
    // Path to the shell script for Ubuntu
    $scriptFile = "StartCAM-webstream.sh";
    
    // Make sure the script is executable
    chmod($scriptFile, 0755);
    
    // Execute the shell script in the background
    exec("nohup ./$scriptFile > /dev/null 2>&1 &");
} else {
    // Default to Windows behavior
    $batFile = "StartCAM-webstream.bat";
    // Execute the .bat file
    exec("start /B $batFile");
}

echo "Stream started successfully.";
?>