<?php
$servername = "localhost";
$username = "root";  // Change this according to your MySQL configuration
$password = "";      // Change this according to your MySQL configuration
$dbname = "connectors.db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
