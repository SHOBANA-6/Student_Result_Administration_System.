<?php
// Database configuration
$db_host = 'localhost';
$db_user = 'root'; // Default username for Laragon
$db_pass = '';     // Default password for Laragon is empty
$db_name = 'result_system';

// Create a database connection
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Start session for login management
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>