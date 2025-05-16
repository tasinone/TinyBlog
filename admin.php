<?php
session_start();

// Check if database exists
$dbFile = "db.sqlite";
if (!file_exists($dbFile)) {
    header("Location: index.php");
    exit;
}

$db = new SQLite3($dbFile);

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: admin.php");
    exit;
}

// Handle login
$loginError = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    $stmt = $db->prepare('SELECT id, username, password FROM admin WHERE username = :username LIMIT 1');
    $stmt->bindValue(':username', $username, SQLITE3_TEXT);
    $result = $stmt->execute();
    $user = $result->fetchArray(SQLITE3_ASSOC);
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_id'] = $user['id'];
        header("Location: admin.php");
        exit;
    } else {
        $loginError = "Invalid username or password";
    }
}

// Handle add new post
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_post']) && isset($_SESSION['admin_logged_in'])) {
    $content = $_POST['content'];
    
    // Generate timestamp
    $now = new DateTime();
    $months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    $day = $now->format('j');
    $month = $months[$now->format('n') - 1];
    $year = $now->format('Y');
    $time = $now->format('g:ia');
    
    $timestamp = "$day $month, $year at $time";
    
    $stmt = $db->prepare('INSERT INTO posts (content, timestamp) VALUES (:content, :timestamp)');
    $stmt->bindValue(':content', $content, SQLITE3_TEXT);
    $stmt->bindValue(':timestamp', $timestamp, SQLITE3_TEXT);
    $stmt->execute();
    
    header("Location: admin.php");
    exit;
}

// Handle edit post
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_post']) && isset($_SESSION['admin_logged_in'])) {
    $postId = $_POST['post_id'];
    $content = $_POST['content'];
    
    $stmt = $db->prepare('UPDATE posts SET content = :content WHERE id = :id');
    $stmt->bindValue(':content', $content, SQLITE3_TEXT);
    $stmt->bindValue(':id', $postId, SQLITE3_INTEGER);
    $stmt->execute();
    
    header("Location: admin.php");
    exit;
}

// Handle delete post
if (isset($_GET['delete']) && isset($_SESSION['admin_logged_in'])) {
    $postId = $_GET['delete'];
    
    $stmt = $db->prepare('DELETE FROM posts WHERE id = :id');
    $stmt->bindValue(':id', $postId, SQLITE3_INTEGER);
    $stmt->execute();
    
    header("Location: admin.php");
    exit;
}

// Handle change admin credentials
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_credentials']) && isset($_SESSION['admin_logged_in'])) {
    $newUsername = $_POST['new_username'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];
    
    if ($newPassword === $confirmPassword) {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        $stmt = $db->prepare('UPDATE admin SET username = :username, password = :password WHERE id = :id');
        $stmt->bindValue(':username', $newUsername, SQLITE3_TEXT);
        $stmt->bindValue(':password', $hashedPassword, SQLITE3_TEXT);
        $stmt->bindValue(':id', $_SESSION['admin_id'], SQLITE3_INTEGER);
        $stmt->execute();
        
        $credentialSuccess = "Admin credentials updated successfully!";
    } else {
        $credentialError = "Passwords do not match!";
    }
}

// Get all posts if logged in
$posts = [];
if (isset($_SESSION['admin_logged_in'])) {
    $results = $db->query('SELECT id, content, timestamp FROM posts ORDER BY id DESC');
    while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
        $posts[] = $row;
    }
}

// Get post for editing
$editPostData = null;
if (isset($_GET['edit']) && isset($_SESSION['admin_logged_in'])) {
    $postId = $_GET['edit'];
    
    $stmt = $db->prepare('SELECT id, content, timestamp FROM posts WHERE id = :id');
    $stmt->bindValue(':id', $postId, SQLITE3_INTEGER);
    $result = $stmt->execute();
    $editPostData = $result->fetchArray(SQLITE3_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <title>TinyBlog Admin</title>
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
    .admin-panel {
      background-color: #fff;
      border-radius: 8px;
      padding: 20px;
      margin-bottom: 20px;
      box-shadow: 0 1px 3px rgba(0,0,0,0.1);
      width: 100%;
      box-sizing: border-box;
    }
    .login-form {
      background-color: #fff;
      border-radius: 8px;
      padding: 20px;
      box-shadow: 0 1px 3px rgba(0,0,0,0.1);
      max-width: 100%;
      margin: 40px auto;
      box-sizing: border-box;
    }
    .error {
      color: #e74c3c;
      margin-bottom: 15px;
    }
    .success {
      color: #2ecc71;
      margin-bottom: 15px;
    }
    .logout {
      color: #1EAEDB;
      cursor: pointer;
      text-decoration: none;
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
    .action-links {
      margin-left: 10px;
    }
    .edit-link, .delete-link {
      color: #1EAEDB;
      text-decoration: none;
      margin-left: 5px;
    }
    .delete-link {
      color: #e74c3c;
    }
    .admin-actions {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 15px;
      flex-wrap: wrap;
    }
    .admin-nav {
      display: flex;
      gap: 15px;
      flex-wrap: wrap;
      margin-top: 10px;
    }
    .admin-nav a {
      color: #1EAEDB;
      text-decoration: none;
    }
    input[type="text"],
    input[type="password"],
    textarea {
      width: 100%;
      box-sizing: border-box;
    }
    .button, button, input[type="submit"] {
      display: inline-block;
      height: 38px;
      padding: 0 30px;
      text-align: center;
      font-size: 11px;
      font-weight: 600;
      line-height: 38px;
      letter-spacing: .1rem;
      text-transform: uppercase;
      text-decoration: none;
      white-space: nowrap;
      background-color: transparent;
      border-radius: 4px;
      border: 1px solid #bbb;
      cursor: pointer;
      box-sizing: border-box;
    }
    .read-more, .read-less {
      color: #1EAEDB;
      cursor: pointer;
      text-decoration: underline;
    }
    
    /* Media queries for better responsiveness */
    @media (max-width: 550px) {
      .admin-actions {
        flex-direction: column;
        align-items: flex-start;
      }
      .admin-nav {
        margin-top: 15px;
      }
      body {
        font-size: 14px;
      }
      .logo-text {
        font-size: 22px;
      }
      .button, button, input[type="submit"] {
        width: 100%;
        margin-bottom: 10px;
      }
    }
  </style>
</head>
<body>
  <header>
    <div class="logo-text">DesiBlog Admin</div>
  </header>

  <div class="container">
    <?php if (!isset($_SESSION['admin_logged_in'])): ?>
      <!-- Login Form -->
      <div class="login-form">
        <h4>Admin Login</h4>
        <?php if ($loginError): ?>
          <div class="error"><?php echo $loginError; ?></div>
        <?php endif; ?>
        <form method="post">
          <div class="row">
            <div class="twelve columns">
              <label for="username">Username</label>
              <input class="u-full-width" type="text" id="username" name="username" required>
            </div>
          </div>
          <div class="row">
            <div class="twelve columns">
              <label for="password">Password</label>
              <input class="u-full-width" type="password" id="password" name="password" required>
            </div>
          </div>
          <input class="button-primary" type="submit" name="login" value="Login">
        </form>
      </div>
    <?php else: ?>
      <!-- Admin Panel -->
      <div class="admin-panel">
        <div class="admin-actions">
          <h4><?php echo isset($editPostData) ? 'Edit Post' : 'Create New Post'; ?></h4>
          <div class="admin-nav">
            <a href="index.php">View Blog</a>
            <a href="#" onclick="showChangeCredentials()">Change Credentials</a>
            <a href="admin.php?logout=1" class="logout">Logout</a>
          </div>
        </div>
        
        <!-- Change Credentials Form (Hidden by default) -->
        <div id="credentials-form" style="display: none; margin-bottom: 20px;">
          <h5>Change Admin Credentials</h5>
          <?php if (isset($credentialError)): ?>
            <div class="error"><?php echo $credentialError; ?></div>
          <?php endif; ?>
          <?php if (isset($credentialSuccess)): ?>
            <div class="success"><?php echo $credentialSuccess; ?></div>
          <?php endif; ?>
          <form method="post">
            <div class="row">
              <div class="twelve columns">
                <label for="new_username">New Username</label>
                <input class="u-full-width" type="text" id="new_username" name="new_username" required>
              </div>
            </div>
            <div class="row">
              <div class="twelve columns">
                <label for="new_password">New Password</label>
                <input class="u-full-width" type="password" id="new_password" name="new_password" required>
              </div>
            </div>
            <div class="row">
              <div class="twelve columns">
                <label for="confirm_password">Confirm Password</label>
                <input class="u-full-width" type="password" id="confirm_password" name="confirm_password" required>
              </div>
            </div>
            <div class="row">
              <div class="twelve columns">
                <input class="button-primary" type="submit" name="change_credentials" value="Update Credentials">
                <button type="button" onclick="hideChangeCredentials()" class="button">Cancel</button>
              </div>
            </div>
          </form>
        </div>
        
        <!-- Post Form -->
        <form method="post">
          <?php if (isset($editPostData)): ?>
            <input type="hidden" name="post_id" value="<?php echo $editPostData['id']; ?>">
          <?php endif; ?>
          <div class="row">
            <div class="twelve columns">
              <textarea class="u-full-width" name="content" placeholder="Write your post here..." required style="min-height: 150px;"><?php echo isset($editPostData) ? htmlspecialchars($editPostData['content']) : ''; ?></textarea>
              <small>* You can use HTML tags and emojis in your post</small>
            </div>
          </div>
          <input class="button-primary" type="submit" name="<?php echo isset($editPostData) ? 'edit_post' : 'add_post'; ?>" value="<?php echo isset($editPostData) ? 'Update Post' : 'Add Post'; ?>">
          <?php if (isset($editPostData)): ?>
            <a href="admin.php" class="button">Cancel</a>
          <?php endif; ?>
        </form>
      </div>
      
      <!-- List of posts -->
      <h4>Your Posts</h4>
      <?php foreach ($posts as $post): ?>
        <div class="post" id="post<?php echo $post['id']; ?>">
          <div class="timestamp">
            <?php echo htmlspecialchars($post['timestamp']); ?>
            <span class="action-links">
              <a href="admin.php?edit=<?php echo $post['id']; ?>" class="edit-link">Edit</a>
              <a href="admin.php?delete=<?php echo $post['id']; ?>" class="delete-link" onclick="return confirm('Are you sure you want to delete this post?')">Delete</a>
            </span>
          </div>
          <div class="post-content">
            <?php if (mb_strlen($post['content']) > 360): ?>
              <div class="truncated-content-<?php echo $post['id']; ?>">
                <?php echo htmlspecialchars(mb_substr($post['content'], 0, 360)); ?>...
                <br><span class="read-more" onclick="expandAdminPost(<?php echo $post['id']; ?>)">See more</span>
              </div>
              <div class="full-content-<?php echo $post['id']; ?>" style="display: none;">
                <?php echo $post['content']; ?>
                <br><span class="read-less" onclick="collapseAdminPost(<?php echo $post['id']; ?>)">See less</span>
              </div>
            <?php else: ?>
              <?php echo $post['content']; ?>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <script>
    function showChangeCredentials() {
      document.getElementById('credentials-form').style.display = 'block';
    }
    
    function hideChangeCredentials() {
      document.getElementById('credentials-form').style.display = 'none';
    }
    
    // Show credentials form if there was an error or success message
    <?php if (isset($credentialError) || isset($credentialSuccess)): ?>
      document.getElementById('credentials-form').style.display = 'block';
    <?php endif; ?>
    
    // Function to expand post in admin panel
    function expandAdminPost(postId) {
      document.querySelector(`.truncated-content-${postId}`).style.display = 'none';
      document.querySelector(`.full-content-${postId}`).style.display = 'block';
    }

    // Function to collapse post in admin panel
    function collapseAdminPost(postId) {
      document.querySelector(`.truncated-content-${postId}`).style.display = 'block';
      document.querySelector(`.full-content-${postId}`).style.display = 'none';
    }
  </script>
</body>
</html>