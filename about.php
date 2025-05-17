<?php
// about.php - About page
require_once 'includes/header.php';
?>

<div class="row">
    <div class="twelve columns">
        <div class="about-container">
            <h3>About TinyBlog</h3>
            
            <div class="about-content">
                <p>TinyBlog is a lightweight, Facebook-style blog system designed for simplicity and ease of use. It was built using:</p>
                
                <ul>
                    <li>PHP for backend functionality</li>
                    <li>Skeleton CSS for minimal, lightweight styling</li>
                    <li>SQLite for database (no setup required)</li>
                    <li>Summernote editor for content creation</li>
                </ul>
                
                <p>This blog features a timeline interface similar to social media platforms, with expandable posts, like/share/view counters, and an easy-to-use admin interface.</p>
                
                <p>Edit this page in the <code>about.php</code> file to add your own content.</p>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>