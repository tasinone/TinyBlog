<?php
// includes/db.php - Database connection and initialization

// Define a constant for the database path - using absolute path
define('DB_PATH', dirname(__DIR__) . '/db.sqlite');

// Create/connect to SQLite database
$db = new SQLite3(DB_PATH);

// Check if database tables exist, if not create them
$db->exec("
    CREATE TABLE IF NOT EXISTS posts (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        content TEXT NOT NULL,
        timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
        views INTEGER DEFAULT 0
    );
    
    CREATE TABLE IF NOT EXISTS comments (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        post_id INTEGER,
        comment TEXT NOT NULL,
        timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (post_id) REFERENCES posts(id)
    );
    
    CREATE TABLE IF NOT EXISTS likes (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        post_id INTEGER,
        ip_address TEXT,
        timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (post_id) REFERENCES posts(id)
    );
    
    CREATE TABLE IF NOT EXISTS admin (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        email TEXT NOT NULL,
        password TEXT NOT NULL
    );
");

// Check if admin credentials exist, if not create default ones
$admin = $db->query("SELECT COUNT(*) as count FROM admin");
$admin_exists = $admin->fetchArray(SQLITE3_ASSOC)['count'];

if ($admin_exists == 0) {
    // Default admin credentials: admin@login.com / admin123
    $default_email = 'admin@login.com';
    $default_password = password_hash('admin123', PASSWORD_DEFAULT);
    $db->exec("INSERT INTO admin (email, password) VALUES ('$default_email', '$default_password')");
}

// Function to sanitize input data
function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// Function to check if admin is logged in
function is_admin() {
    return isset($_SESSION['admin']) && $_SESSION['admin'] === true;
}