<?php
// archive.php - Archive page
require_once 'includes/header.php';

// Get all archives by month and year
$archives = get_archives($db);
?>

<div class="row">
    <div class="twelve columns">
        <div class="archive-container">
            <h3>Blog Archive</h3>
            
            <?php if(empty($archives)): ?>
                <p>No posts to archive yet.</p>
            <?php else: ?>
                <ul class="archive-list">
                    <?php foreach($archives as $month_year => $data): ?>
                        <?php 
                            $last_post_id = get_last_post_id_by_month($db, $data['date']);
                            $post_anchor = $last_post_id ? generate_post_anchor($last_post_id) : '';
                        ?>
                        <li>
                            <a href="index.php?post=<?php echo $last_post_id; ?>#<?php echo $post_anchor; ?>">
                                <?php echo $month_year; ?> (<?php echo $data['count']; ?> posts)
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>