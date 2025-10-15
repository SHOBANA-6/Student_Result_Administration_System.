<?php
require '../php/db_connect.php';

// Unset all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Redirect to the login page
header("Location: ../admin_login.php");
exit();
?>