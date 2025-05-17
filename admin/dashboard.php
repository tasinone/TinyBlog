<?php
// admin/dashboard.php - Admin dashboard with editor
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if logged in
if(!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header('Location: index.php');
    exit;
}

// Load database connection and functions
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once 'functions.php';

// Handle site title update
if(isset($_POST['update_site_title'])) {
    $new_title = $_POST['site_title'];
    
    $stmt = $db->prepare("UPDATE settings SET value = :value WHERE name = 'site_title'");
    $stmt->bindValue(':value', $new_title, SQLITE3_TEXT);
    $result = $stmt->execute();
    
    if($result) {
        $title_message = 'Site title updated successfully!';
    } else {
        $title_error = 'Failed to update site title.';
    }
}

// Handle logo upload
if(isset($_POST['upload_logo'])) {
    $upload_dir = '../assets/img/';
    $logo_name = 'logo.png';
    $upload_file = $upload_dir . $logo_name;
    
    // Check if the uploaded file is an image
    $check = getimagesize($_FILES["logo_file"]["tmp_name"]);
    if($check !== false) {
        // Remove old logo if exists
        if(file_exists($upload_file)) {
            unlink($upload_file);
        }
        
        // Upload new logo
        if(move_uploaded_file($_FILES["logo_file"]["tmp_name"], $upload_file)) {
            $logo_message = 'Logo uploaded successfully!';
        } else {
            $logo_error = 'Failed to upload logo. Please try again.';
        }
    } else {
        $logo_error = 'Uploaded file is not a valid image.';
    }
}

// Handle logo removal
if(isset($_POST['remove_logo'])) {
    $logo_path = '../assets/img/logo.png';
    if(file_exists($logo_path)) {
        if(unlink($logo_path)) {
            $logo_message = 'Logo removed successfully!';
        } else {
            $logo_error = 'Failed to remove logo. Please try again.';
        }
    } else {
        $logo_error = 'No logo found to remove.';
    }
}

// Handle settings update
if(isset($_POST['update_settings'])) {
    $new_email = $_POST['admin_email'];
    $new_password = $_POST['admin_password'];
    
    $result = change_admin_credentials($db, $new_email, $new_password);
    
    if($result === true) {
        $settings_message = 'Admin credentials updated successfully!';
    } else {
        $settings_error = $result;
    }
}

// Handle post deletion
if(isset($_GET['delete']) && !empty($_GET['delete'])) {
    $post_id = intval($_GET['delete']);
    
    // Delete post and related data
    $db->exec("DELETE FROM posts WHERE id = $post_id");
    $db->exec("DELETE FROM likes WHERE post_id = $post_id");
    $db->exec("DELETE FROM comments WHERE post_id = $post_id");
    
    // Redirect to dashboard
    header('Location: dashboard.php?deleted=1');
    exit;
}

// Handle form submission for new post or edit
$editing = false;
$post_content = '';
$post_id = 0;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_POST['update_settings']) && !isset($_POST['update_site_title']) && !isset($_POST['upload_logo']) && !isset($_POST['remove_logo'])) {
    $content = $_POST['content'] ?? '';

    // Check if content is empty or just whitespace/HTML tags
    $stripped_content = trim(strip_tags($content));

    if (empty($stripped_content)) {
        $error_message = "Post content cannot be empty!";
    } else {
        if (isset($_POST['post_id']) && !empty($_POST['post_id'])) {
            // Update existing post
            $post_id = intval($_POST['post_id']);

            $stmt = $db->prepare("UPDATE posts SET content = :content WHERE id = :id");
            $stmt->bindValue(':content', $content, SQLITE3_TEXT);
            $stmt->bindValue(':id', $post_id, SQLITE3_INTEGER);

            $result = $stmt->execute();

            if ($result) {
                $success_message = 'Post updated successfully!';
            } else {
                $error_message = 'Failed to update post. Please try again.';
            }
        } else {
            // Create new post
            $stmt = $db->prepare("INSERT INTO posts (content, timestamp, views) VALUES (:content, datetime('now'), 0)");
            $stmt->bindValue(':content', $content, SQLITE3_TEXT);

            $result = $stmt->execute();

            if ($result) {
                $success_message = 'New post created successfully!';
            } else {
                $error_message = 'Failed to create post. Please try again.';
            }
        }
    }
}

// Check if editing a post 
if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    $post_id = intval($_GET['edit']);
    $post = get_post($db, $post_id);

    if ($post) {
        $editing = true;
        $post_content = $post['content'];
    }
}

// Get current admin email
$admin_result = $db->query("SELECT email FROM admin WHERE id = 1");
$admin_data = $admin_result->fetchArray(SQLITE3_ASSOC);
$current_email = $admin_data['email'];

// Get current site title
$title_result = $db->query("SELECT value FROM settings WHERE name = 'site_title'");
if ($title_result) {
    $title_data = $title_result->fetchArray(SQLITE3_ASSOC);
    $current_title = $title_data ? $title_data['value'] : 'TinyBlog';
} else {
    // Create settings table and site_title entry if not exists
    $db->exec("CREATE TABLE IF NOT EXISTS settings (name TEXT PRIMARY KEY, value TEXT)");
    $db->exec("INSERT OR IGNORE INTO settings (name, value) VALUES ('site_title', 'TinyBlog')");
    $current_title = 'TinyBlog';
}

// Check if logo exists
$logo_path = '../assets/img/logo.png';
$logo_exists = file_exists($logo_path);

// Debug database path
echo "<!-- Using database: " . DB_PATH . " -->";

// Count posts in database
$count_result = $db->query("SELECT COUNT(*) as count FROM posts");
$post_count = $count_result->fetchArray(SQLITE3_ASSOC)['count'];
echo "<!-- Found {$post_count} posts in database -->";

// Get recent posts for reference - requery for fresh results
$recent_posts = $db->query("SELECT id, substr(content, 1, 100) as preview, timestamp FROM posts ORDER BY timestamp DESC LIMIT 5");

// Check if viewing settings
$show_settings = isset($_GET['settings']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($current_title); ?> Admin - Dashboard</title>
    
    <!-- Skeleton CSS -->
    <link rel="stylesheet" href="../assets/css/normalize.css">
    <link rel="stylesheet" href="../assets/css/skeleton.css">
    
    <!-- Google Fonts - Hind Siliguri for Bangla support -->
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom Admin CSS -->
    <style>
        body {
            background-color: #f9f9f9;
            font-family: 'Hind Siliguri', sans-serif;
        }
        .admin-header {
            background: white;
            border-bottom: 1px solid #e1e1e1;
            padding: 15px 0;
            margin-bottom: 30px;
        }
        .admin-header h2 {
            margin: 0;
        }
        .admin-header .admin-actions {
            text-align: right;
        }
        .admin-content {
            background: white;
            border: 1px solid #e1e1e1;
            border-radius: 4px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        .success-message {
            background-color: #dff0d8;
            color: #3c763d;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .error-message {
            background-color: #f2dede;
            color: #a94442;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .recent-posts {
            margin-top: 30px;
        }
        .recent-posts h4 {
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .post-item {
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        .post-item:last-child {
            border-bottom: none;
        }
        .post-item .timestamp {
            font-size: 12px;
            color: #777;
        }
        .post-item .preview {
            margin-top: 5px;
            color: #555;
        }
        .post-item .actions {
            margin-top: 5px;
        }
        .post-item .actions a {
            font-size: 12px;
            margin-right: 10px;
            text-decoration: none;
        }
        .settings-form {
            max-width: 400px;
            margin-bottom: 30px;
        }
        .settings-form label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .settings-form input {
            margin-bottom: 15px;
        }
        .settings-section {
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        .settings-section:last-child {
            border-bottom: none;
        }
        .logo-preview {
            max-width: 200px;
            max-height: 100px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            padding: 5px;
        }
        textarea {
            min-height: 300px;
            width: 100%;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Admin Header -->
        <div class="admin-header">
            <div class="row">
                <div class="six columns">
                    <h2><?php echo htmlspecialchars($current_title); ?> Admin</h2>
                </div>
                <div class="six columns admin-actions">
                    <a href="../index.php" class="button">View Blog</a>
                    <a href="dashboard.php?settings=1" class="button">Settings</a>
                    <a href="logout.php" class="button button-primary">Logout</a>
                </div>
            </div>
        </div>
        
        <?php if($show_settings): ?>
            <!-- Settings Content -->
            <div class="admin-content">
                <h3>Site Settings</h3>
                
                <!-- Site Title Settings -->
                <div class="settings-section">
                    <h4>Site Title</h4>
                    
                    <?php if(isset($title_message)): ?>
                        <div class="success-message">
                            <?php echo $title_message; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if(isset($title_error)): ?>
                        <div class="error-message">
                            <?php echo $title_error; ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="" class="settings-form">
                        <div class="form-group">
                            <label for="site_title">Site Title:</label>
                            <input type="text" id="site_title" name="site_title" value="<?php echo htmlspecialchars($current_title); ?>" required>
                        </div>
                        
                        <button type="submit" name="update_site_title" class="button-primary">Update Site Title</button>
                    </form>
                </div>
                
                <!-- Logo Settings -->
                <div class="settings-section">
                    <h4>Site Logo</h4>
                    
                    <?php if(isset($logo_message)): ?>
                        <div class="success-message">
                            <?php echo $logo_message; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if(isset($logo_error)): ?>
                        <div class="error-message">
                            <?php echo $logo_error; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if($logo_exists): ?>
                        <p>Current logo:</p>
                        <img src="../assets/img/logo.png?v=<?php echo time(); ?>" alt="Site Logo" class="logo-preview">
                        
                        <form method="POST" action="" class="settings-form">
                            <button type="submit" name="remove_logo" class="button">Remove Logo</button>
                        </form>
                    <?php else: ?>
                        <p>No logo uploaded. The site title will be displayed instead.</p>
                    <?php endif; ?>
                    
                    <form method="POST" action="" enctype="multipart/form-data" class="settings-form">
                        <div class="form-group">
                            <label for="logo_file">Upload Logo:</label>
                            <input type="file" id="logo_file" name="logo_file" accept="image/*" required>
                            <small style="color: #777;">Recommended size: 200x50 pixels. PNG format with transparent background works best.</small>
                        </div>
                        
                        <button type="submit" name="upload_logo" class="button-primary">Upload Logo</button>
                    </form>
                </div>
                
                <!-- Admin Credentials Settings -->
                <div class="settings-section">
                    <h4>Admin Credentials</h4>
                    
                    <?php if(isset($settings_message)): ?>
                        <div class="success-message">
                            <?php echo $settings_message; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if(isset($settings_error)): ?>
                        <div class="error-message">
                            <?php echo $settings_error; ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="" class="settings-form">
                        <div class="form-group">
                            <label for="admin_email">Admin Email:</label>
                            <input type="email" id="admin_email" name="admin_email" value="<?php echo htmlspecialchars($current_email); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="admin_password">New Password:</label>
                            <input type="password" id="admin_password" name="admin_password" placeholder="Enter new password" required>
                            <small style="color: #777;">Password must be at least 8 characters long.</small>
                        </div>
                        
                        <button type="submit" name="update_settings" class="button-primary">Update Credentials</button>
                    </form>
                </div>
                
                <a href="dashboard.php" class="button">Back to Dashboard</a>
            </div>
        <?php else: ?>
            <!-- Admin Content -->
            <div class="admin-content">
                <?php if(isset($success_message)): ?>
                    <div class="success-message">
                        <?php echo $success_message; ?>
                    </div>
                <?php endif; ?>
                
                <?php if(isset($error_message)): ?>
                    <div class="error-message">
                        <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>
                
                <?php if(isset($_GET['deleted']) && $_GET['deleted'] == 1): ?>
                    <div class="success-message">
                        Post deleted successfully!
                    </div>
                <?php endif; ?>
                
                <h3><?php echo $editing ? 'Edit Post' : 'Create New Post'; ?></h3>
                
                <form method="POST" action="">
                    <?php if($editing): ?>
                        <input type="hidden" name="post_id" value="<?php echo $post_id; ?>">
                    <?php endif; ?>
                    
                    <textarea name="content" placeholder="Start writing your post here..."><?php echo htmlspecialchars($post_content); ?></textarea>
                    
                    <button type="submit" class="button-primary">
                        <?php echo $editing ? 'Update Post' : 'Publish Post'; ?>
                    </button>
                    
                    <?php if($editing): ?>
                        <a href="dashboard.php" class="button">Cancel Editing</a>
                    <?php endif; ?>
                </form>
            </div>
            
            <!-- Recent Posts -->
            <div class="admin-content recent-posts">
                <h4>Recent Posts (<?php echo $post_count; ?> total)</h4>
                
                <?php if($post_count == 0): ?>
                    <p>No posts yet.</p>
                <?php else: ?>
                    <?php
                    // Requery to ensure fresh results
                    $recent_posts = $db->query("SELECT id, substr(content, 1, 100) as preview, timestamp FROM posts ORDER BY timestamp DESC LIMIT 5");
                    while($post = $recent_posts->fetchArray(SQLITE3_ASSOC)): 
                    ?>
                        <div class="post-item">
                            <div class="timestamp">
                                <?php echo format_timestamp($post['timestamp']); ?>
                            </div>
                            <div class="preview">
                                <?php echo strip_tags(substr($post['preview'], 0, 100)) . '...'; ?>
                            </div>
                            <div class="actions">
                                <a href="dashboard.php?edit=<?php echo $post['id']; ?>">Edit</a>
                                <a href="dashboard.php?delete=<?php echo $post['id']; ?>" 
                                   onclick="return confirm('Are you sure you want to delete this post?')">Delete</a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- JavaScript Dependencies -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
</body>
</html>