<?php
// debug.php - Place this in your root directory to diagnose the issue

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Starting diagnostic...<br>";

// Test 1: Check if files exist
echo "1. Checking file existence:<br>";
$files_to_check = [
    'includes/db.php',
    'includes/functions.php', 
    'includes/header.php',
    'admin/dashboard.php',
    'admin/functions.php'
];

foreach($files_to_check as $file) {
    echo "- $file: " . (file_exists($file) ? "EXISTS" : "MISSING") . "<br>";
}

// Test 2: Try to load database
echo "<br>2. Testing database connection:<br>";
try {
    require_once 'includes/db.php';
    echo "- Database connection: SUCCESS<br>";
} catch(Exception $e) {
    echo "- Database error: " . $e->getMessage() . "<br>";
}

// Test 3: Try to load functions
echo "<br>3. Testing functions file:<br>";
try {
    require_once 'includes/functions.php';
    echo "- Functions loaded: SUCCESS<br>";
    
    // Test if functions exist
    $functions_to_check = ['format_timestamp', 'is_admin', 'get_post'];
    foreach($functions_to_check as $func) {
        echo "- $func(): " . (function_exists($func) ? "EXISTS" : "MISSING") . "<br>";
    }
} catch(Exception $e) {
    echo "- Functions error: " . $e->getMessage() . "<br>";
}

// Test 4: Try to start session
echo "<br>4. Testing session:<br>";
try {
    session_start();
    echo "- Session: SUCCESS<br>";
} catch(Exception $e) {
    echo "- Session error: " . $e->getMessage() . "<br>";
}

// Test 5: Try loading admin functions
echo "<br>5. Testing admin functions:<br>";
try {
    require_once 'admin/functions.php';
    echo "- Admin functions: SUCCESS<br>";
    echo "- change_admin_credentials(): " . (function_exists('change_admin_credentials') ? "EXISTS" : "MISSING") . "<br>";
} catch(Exception $e) {
    echo "- Admin functions error: " . $e->getMessage() . "<br>";
}

echo "<br>Diagnostic complete. Check above for any errors.";
?>