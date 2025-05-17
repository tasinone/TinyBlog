<?php
// includes/functions.php - Common functions for the blog

// Get base URL for proper path resolution
function get_base_url() {
    // Check if we're in a subdirectory
    $base_dir = dirname($_SERVER['SCRIPT_NAME']);
    $base_url = '';
    
    // If in admin folder, go up one level
    if (strpos($_SERVER['SCRIPT_NAME'], '/admin/') !== false) {
        $base_url = '../';
    } else if ($base_dir != '/' && $base_dir != '\\') {
        // We're in a subdirectory of the web root
        $base_url = '/';
    }
    
    return $base_url;
}

// Format timestamp to display in the required format (DD-MM-YYYY at H:MM)
function format_timestamp($timestamp) {
    $date = new DateTime($timestamp);
    return $date->format('d-m-Y \a\t g:ia');
}

// Get month name from timestamp
function get_month_name($timestamp) {
    $date = new DateTime($timestamp);
    return $date->format('F Y');
}

// Get post by ID
function get_post($db, $post_id) {
    $stmt = $db->prepare("SELECT * FROM posts WHERE id = :id");
    $stmt->bindValue(':id', $post_id, SQLITE3_INTEGER);
    $result = $stmt->execute();
    return $result->fetchArray(SQLITE3_ASSOC);
}

// Update post views
function update_views($db, $post_id) {
    $stmt = $db->prepare("UPDATE posts SET views = views + 1 WHERE id = :id");
    $stmt->bindValue(':id', $post_id, SQLITE3_INTEGER);
    $stmt->execute();
}

// Check if a post is liked by current IP
function is_post_liked($db, $post_id) {
    $ip = $_SERVER['REMOTE_ADDR'];
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM likes WHERE post_id = :post_id AND ip_address = :ip");
    $stmt->bindValue(':post_id', $post_id, SQLITE3_INTEGER);
    $stmt->bindValue(':ip', $ip, SQLITE3_TEXT);
    $result = $stmt->execute();
    return $result->fetchArray(SQLITE3_ASSOC)['count'] > 0;
}

// Get like count for a post
function get_like_count($db, $post_id) {
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM likes WHERE post_id = :post_id");
    $stmt->bindValue(':post_id', $post_id, SQLITE3_INTEGER);
    $result = $stmt->execute();
    return $result->fetchArray(SQLITE3_ASSOC)['count'];
}

// Get view count for a post
function get_view_count($db, $post_id) {
    $stmt = $db->prepare("SELECT views FROM posts WHERE id = :id");
    $stmt->bindValue(':id', $post_id, SQLITE3_INTEGER);
    $result = $stmt->execute();
    return $result->fetchArray(SQLITE3_ASSOC)['views'];
}

// Generate unique ID for a post based on timestamp for anchoring
function generate_post_anchor($post_id) {
    return 'post-' . $post_id;
}

// Truncate HTML content safely to a specific height
// This is for display purposes - the actual truncation happens via CSS/JS
function prepare_post_content($content) {
    // Remove potentially harmful scripts
    $content = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', "", $content);
    return $content;
}

// Get archives by month and year
function get_archives($db) {
    $archives = array();
    $result = $db->query("SELECT strftime('%Y-%m', timestamp) as month, COUNT(*) as count FROM posts GROUP BY month ORDER BY month DESC");
    
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $date = new DateTime($row['month'] . '-01');
        $month_year = $date->format('F Y');
        $archives[$month_year] = array(
            'date' => $row['month'],
            'count' => $row['count']
        );
    }
    
    return $archives;
}

// Get last post id of a specific month
function get_last_post_id_by_month($db, $month) {
    $stmt = $db->prepare("SELECT id FROM posts WHERE strftime('%Y-%m', timestamp) = :month ORDER BY timestamp DESC LIMIT 1");
    $stmt->bindValue(':month', $month, SQLITE3_TEXT);
    $result = $stmt->execute();
    $post = $result->fetchArray(SQLITE3_ASSOC);
    return $post ? $post['id'] : null;
}

// NOTE: The is_admin() function is declared in includes/db.php
// Do not redeclare it here to avoid fatal errors