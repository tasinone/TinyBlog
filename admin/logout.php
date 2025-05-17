<?php
// admin/logout.php - Handle admin logout
session_start();

// Destroy session
$_SESSION = array();
session_destroy();

// Redirect to login page
header('Location: index.php');
exit;