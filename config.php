<?php
$servername = "localhost";
$username = "userhere";
$password = "passwordhere";
$dbname = "user_auth";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Define your encryption key (32 bytes long)
define('ENCRYPTION_KEY', 'your-32-byte-long-encryption-key');
?>
