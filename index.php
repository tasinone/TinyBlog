<?php
// Check if the database exists, if not create it
$dbFile = "db.sqlite";
$isNewDB = !file_exists($dbFile);

try {
    $db = new SQLite3($dbFile);
    
    // Create tables if it's a new database
    if ($isNewDB) {
        // Create posts table
        $db->exec('
            CREATE TABLE posts (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                content TEXT NOT NULL,
                timestamp TEXT NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ');
        
        // Create admin credentials table
        $db->exec('
            CREATE TABLE admin (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username TEXT NOT NULL,
                password TEXT NOT NULL
            )
        ');
        
        // Insert default admin credentials
        $db->exec('
            INSERT INTO admin (username, password) 
            VALUES ("admin", "' . password_hash("password", PASSWORD_DEFAULT) . '")
        ');
        
        // Insert sample posts
        $db->exec('
            INSERT INTO posts (content, timestamp) 
            VALUES 
            ("Welcome to DesiBlog! This is a simple blog platform where you can share your thoughts and ideas. The platform is designed to be clean, responsive, and easy to use. You can write posts, edit them, and delete them as needed.", "12 January, 2025 at 12:30pm"),
            ("This is a demonstration of a longer post. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam eget felis eget nunc lobortis mattis aliquam faucibus. Vivamus magna justo, lacinia eget consectetur sed, convallis at tellus. Pellentesque in ipsum id orci porta dapibus. Cras ultricies ligula sed magna dictum porta. Cras ultricies ligula sed magna dictum porta. Sed porttitor lectus nibh. Vivamus magna justo, lacinia eget consectetur sed, convallis at tellus. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam eget felis eget nunc lobortis mattis aliquam faucibus.", "15 January, 2025 at 10:45am")
        ');
    }
} catch (Exception $e) {
    die("Database error: " . $e->getMessage());
}

// Get all posts
$posts = [];
$results = $db->query('SELECT id, content, timestamp FROM posts ORDER BY id DESC');
while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
    $posts[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <title>TinyBlog</title>
  <!-- Skeleton CSS -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/skeleton/2.0.4/skeleton.min.css">
  <!-- Google Font - Hind Siliguri for Bangla -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@300;400;500;600;700&display=swap">
  <style>
    body {
      margin: 0;
      padding: 0;
      background-color: #f5f5f5;
      font-family: 'Hind Siliguri', sans-serif;
      font-size: 16px;  /* Base font size for better readability on mobile */
    }
    .container {
      width: 100%;
      /*max-width: 540px;*/
      padding: 0 15px;
      margin: 0 auto;
      box-sizing: border-box;
    }
    header {
      position: sticky;
      top: 0;
      z-index: 1000;
      text-align: center;
      padding: 20px 0;
      background-color: #fff;
      border-bottom: 1px solid #e0e0e0;
      margin-bottom: 20px;
      width: 100%;
    }
    .logo-text {
      font-size: 24px;
      font-weight: bold;
    }
    .logo-img {
      display: none;
      height: 55px;
    }
    .post {
      background-color: #fff;
      border-radius: 8px;
      padding: 15px;
      margin-bottom: 20px;
      box-shadow: 0 1px 3px rgba(0,0,0,0.1);
      width: 100%;
      box-sizing: border-box;
    }
    .timestamp {
      font-style: italic;
      font-size: 0.8em;
      color: #777;
      margin-bottom: 10px;
    }
    .post-content {
      margin-bottom: 10px;
      /*white-space: pre-wrap;*/
    }
    
    .post img {
      width: 100%;
      height: auto;
    }
    .read-more, .read-less {
      color: #1EAEDB;
      cursor: pointer;
      text-decoration: underline;
    }
    .share-btn {
      text-align: right;
      color: #1EAEDB;
      cursor: pointer;
      border-top: 1px solid #e0e0e0;
      padding-top: 10px;
      text-align: center;
    }
    .notification {
      position: fixed;
      bottom: 20px;
      left: 50%;
      transform: translateX(-50%);
      background-color: #333;
      color: white;
      padding: 10px 20px;
      border-radius: 4px;
      opacity: 0;
      transition: opacity 0.3s;
    }
    
    /* Media queries for better responsiveness */
    @media (max-width: 550px) {
      body {
        font-size: 14px;
      }
      .logo-text {
        font-size: 22px;
      }
    }
    
    @media (min-width: 550px) {
      .logo-img {
        height: 65px;
      }
    }
  </style>
</head>
<body>
  <header>
    <div class="logo-text">DesiBlog</div>
    <img class="logo-img" alt="DesiBlog Logo">
  </header>

  <div class="container" id="main-content">
    <?php foreach ($posts as $post): ?>
      <div class="post" id="post<?php echo $post['id']; ?>">
        <div class="timestamp"><?php echo htmlspecialchars($post['timestamp']); ?></div>
        <div class="post-content">
          <?php if (mb_strlen($post['content']) > 360): ?>
            <div class="truncated-content">
              <?php echo htmlspecialchars(mb_substr($post['content'], 0, 360)); ?>...
              <br><span class="read-more" onclick="expandPost(<?php echo $post['id']; ?>)">See more</span>
            </div>
            <div class="full-content" style="display: none;">
              <?php echo $post['content']; ?>
              <br><span class="read-less" onclick="collapsePost(<?php echo $post['id']; ?>)">See less</span>
            </div>
          <?php else: ?>
            <?php echo $post['content']; ?>
          <?php endif; ?>
        </div>
        <div class="share-btn" onclick="sharePost(<?php echo $post['id']; ?>)">Share</div>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="notification" id="notification">Link copied!</div>

  <script>
    // Check if logo file exists and display it
    const logoExtensions = ['png', 'jpeg', 'svg'];
    let logoFound = false;
    
    logoExtensions.forEach(ext => {
      const img = new Image();
      img.src = `/logo.${ext}`;
      img.onload = function() {
        if (!logoFound) {
          document.querySelector('.logo-img').src = img.src;
          document.querySelector('.logo-img').style.display = 'inline-block';
          document.querySelector('.logo-text').style.display = 'none';
          logoFound = true;
        }
      };
    });

    // Function to expand post
    function expandPost(postId) {
      const postElement = document.getElementById(`post${postId}`);
      postElement.querySelector('.truncated-content').style.display = 'none';
      postElement.querySelector('.full-content').style.display = 'block';
      
      // Scroll into view for better UX
      setTimeout(() => {
        postElement.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
      }, 100);
    }

    // Function to collapse post
    function collapsePost(postId) {
      const postElement = document.getElementById(`post${postId}`);
      postElement.querySelector('.truncated-content').style.display = 'block';
      postElement.querySelector('.full-content').style.display = 'none';
    }

    // Function to share post
    function sharePost(postId) {
      const url = `${window.location.origin}${window.location.pathname}#post${postId}`;
      navigator.clipboard.writeText(url).then(() => {
        const notification = document.getElementById('notification');
        notification.style.opacity = '1';
        setTimeout(() => {
          notification.style.opacity = '0';
        }, 2500);
      });
    }

    // Check if there's a hash in the URL (for sharing)
    if (window.location.hash) {
      const postId = window.location.hash;
      setTimeout(() => {
        const element = document.querySelector(postId);
        if (element) {
          element.scrollIntoView({ behavior: 'smooth' });
          
          // If this is a truncated post, expand it when directly accessed
          const postNumber = postId.replace('#post', '');
          const postElement = document.getElementById(`post${postNumber}`);
          if (postElement) {
            const truncatedContent = postElement.querySelector('.truncated-content');
            const fullContent = postElement.querySelector('.full-content');
            if (truncatedContent && fullContent) {
              truncatedContent.style.display = 'none';
              fullContent.style.display = 'block';
            }
          }
        }
      }, 100);
    }
  </script>
</body>
</html>