<?php
// includes/header.php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Get site title from settings
$site_title = 'TinyBlog'; // Default title

// Make sure the settings table exists
$db->exec("CREATE TABLE IF NOT EXISTS settings (name TEXT PRIMARY KEY, value TEXT)");
$db->exec("INSERT OR IGNORE INTO settings (name, value) VALUES ('site_title', 'TinyBlog')");

// Now query the settings table
$title_result = $db->query("SELECT value FROM settings WHERE name = 'site_title'");
if ($title_result) {
    $title_data = $title_result->fetchArray(SQLITE3_ASSOC);
    if ($title_data) {
        $site_title = $title_data['value'];
    }
}

// Check if logo exists
$logo_path = 'assets/img/logo.png';
$logo_exists = file_exists($logo_path);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($site_title); ?></title>
    <!-- Skeleton CSS -->
    <link rel="stylesheet" href="assets/css/normalize.css">
    <link rel="stylesheet" href="assets/css/skeleton.css">
    
    <!-- Google Fonts - Hind Siliguri for Bangla support -->
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/custom.css">
    
    <!-- FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <div class="container">
        <header class="header">
            <div class="row">
                <!-- Logo -->
                <div class="six columns">
                    <div class="logo">
                        <a href="index.php">
                            <?php if($logo_exists): ?>
                                <img src="<?php echo $logo_path; ?>" alt="<?php echo htmlspecialchars($site_title); ?> Logo">
                            <?php else: ?>
                                <h2><?php echo htmlspecialchars($site_title); ?></h2>
                            <?php endif; ?>
                        </a>
                    </div>
                </div>
                
                <!-- Navigation -->
                <div class="six columns">
                    <nav class="navigation">
                        <ul class="desktop-menu">
                            <li><a href="about.php">About</a></li>
                            <li><a href="archive.php">Archive</a></li>
                            <li><a href="contact.php">Contact</a></li>
                            <?php if(is_admin()): ?>
                                <li><a href="admin/dashboard.php">Admin</a></li>
                                <li><a href="admin/logout.php">Logout</a></li>
                            <?php endif; ?>
                        </ul>
                        
                        <!-- Hamburger menu for mobile -->
                        <div class="hamburger-menu">
                            <button><i class="fas fa-bars"></i></button>
                            <div class="mobile-menu">
                                <a href="about.php">About</a>
                                <a href="archive.php">Archive</a>
                                <a href="contact.php">Contact</a>
                                <?php if(is_admin()): ?>
                                    <a href="admin/dashboard.php">Admin</a>
                                    <a href="admin/logout.php">Logout</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </nav>
                </div>
            </div>
        </header>
        
        <!-- Main Content Start -->
        <div class="main-content">