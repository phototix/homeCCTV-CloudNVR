<?php
// Secure the script to prevent unauthorized access
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    exit("Unauthorized access");
}

// Path to the .bat file
$batFile = "StartCAM-webstream.bat";

// Execute the .bat file
$output = [];
echo "Stream started successfully.";
exec("start /B $batFile", $output);
?>