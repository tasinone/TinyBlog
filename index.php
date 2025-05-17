<?php
// index.php - Main timeline page

require_once 'includes/header.php';

// Get posts for timeline
$result = $db->query("SELECT * FROM posts ORDER BY timestamp DESC");

// Handle post likes via AJAX
if(isset($_POST['like_post']) && isset($_POST['post_id'])) {
    $post_id = intval($_POST['post_id']);
    $ip = $_SERVER['REMOTE_ADDR'];
    
    // Check if already liked
    $already_liked = is_post_liked($db, $post_id);
    
    if(!$already_liked) {
        // Add new like
        $stmt = $db->prepare("INSERT INTO likes (post_id, ip_address) VALUES (:post_id, :ip)");
        $stmt->bindValue(':post_id', $post_id, SQLITE3_INTEGER);
        $stmt->bindValue(':ip', $ip, SQLITE3_TEXT);
        $stmt->execute();
        echo 'liked';
    } else {
        // Remove existing like
        $stmt = $db->prepare("DELETE FROM likes WHERE post_id = :post_id AND ip_address = :ip");
        $stmt->bindValue(':post_id', $post_id, SQLITE3_INTEGER);
        $stmt->bindValue(':ip', $ip, SQLITE3_TEXT);
        $stmt->execute();
        echo 'unliked';
    }
    exit;
}

// Handle jumping to specific post
$target_post = isset($_GET['post']) ? intval($_GET['post']) : null;

// Get posts for timeline - requery to ensure fresh results
$result = $db->query("SELECT * FROM posts ORDER BY timestamp DESC");
$post_count = 0;
$temp_result = $db->query("SELECT COUNT(*) as count FROM posts");
if ($temp_result) {
    $count_row = $temp_result->fetchArray(SQLITE3_ASSOC);
    $post_count = $count_row['count'];
}
?>

<div class="row">
    <div class="twelve columns">
        <div class="timeline">
            <?php if($post_count == 0): ?>
                <div class="no-posts">
                    <p>No posts yet. Check back later!</p>
                </div>
            <?php else: ?>
                <?php 
                // Reset result pointer
                $result = $db->query("SELECT * FROM posts ORDER BY timestamp DESC");
                
                while($post = $result->fetchArray(SQLITE3_ASSOC)): 
                ?>
                    <?php 
                        $post_id = $post['id'];
                        $like_count = get_like_count($db, $post_id);
                        $view_count = $post['views'];
                        $is_liked = is_post_liked($db, $post_id);
                        $post_anchor = generate_post_anchor($post_id);
                        
                        // Increase view count if this is the target post
                        if($target_post && $target_post == $post_id) {
                            update_views($db, $post_id);
                            $view_count++; // Update for display
                        }
                    ?>
                    <div class="post" id="<?php echo $post_anchor; ?>">
                        <div class="post-header">
                            <span class="timestamp"><?php echo format_timestamp($post['timestamp']); ?></span>
                            <?php if(is_admin()): ?>
                                <span class="admin-actions">
                                    <a href="admin/dashboard.php?edit=<?php echo $post_id; ?>" class="edit-post">Edit</a>
                                    <a href="admin/dashboard.php?delete=<?php echo $post_id; ?>" class="delete-post" 
                                       onclick="return confirm('Are you sure you want to delete this post?')">Delete</a>
                                </span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="post-content">
                            <div class="content-preview">
                                <?php echo prepare_post_content($post['content']); ?>
                            </div>
                            <button class="see-more">See more</button>
                            <button class="see-less" style="display:none;">See less</button>
                        </div>
                        
                        <div class="post-actions">
                            <div class="action like-action <?php echo $is_liked ? 'liked' : ''; ?>" data-post-id="<?php echo $post_id; ?>">
                                <span class="like-count"><?php echo $like_count; ?></span> Like
                            </div>
                            <div class="action share-action" data-post-url="<?php echo $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF']; ?>?post=<?php echo $post_id; ?>#<?php echo $post_anchor; ?>">
                                Share
                            </div>
                            <div class="action view-count">
                                <span class="count"><?php echo $view_count; ?></span> Views
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>