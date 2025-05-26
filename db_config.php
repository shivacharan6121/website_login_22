<?php
// ===== MAC ADDRESS PROTECTION =====
$allowed_mac = '48-F1-7F-31-E6-D8'; // Replace with your actual MAC address

// Get MAC address (works for both Windows and Linux)
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    // Fixed Windows command - properly escaped quotes
    $mac = strtoupper(trim(shell_exec('getmac /FO CSV /NH')));
    $mac = substr($mac, 0, 17); // Extract just the MAC part
} else {
    // Linux/MacOS method
    $mac = shell_exec("cat /sys/class/net/$(ip route show default | awk '/default/ {print $5}')/address");
    $mac = strtoupper(trim($mac));
    $mac = str_replace(':', '-', $mac); // Standardize format
}

if (strpos($mac, $allowed_mac) === false) {
    error_log(date('Y-m-d H:i:s') . " - Unauthorized MAC attempted access\n", 3, __DIR__ . '/security.log');
    die("This system is not licensed to run this software.");
}

// ===== DATABASE CONNECTION =====
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "connectors.db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    die("System maintenance in progress. Please try again later.");
}
?>