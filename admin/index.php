<?php
// admin/index.php - Admin login page
session_start();

// Check if already logged in
if(isset($_SESSION['admin']) && $_SESSION['admin'] === true) {
    header('Location: dashboard.php');
    exit;
}

// Load database connection
require_once '../includes/db.php';

// Handle login form submission
$error = '';
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    
    // Validate inputs
    if(empty($email) || empty($password)) {
        $error = 'Please enter both email and password.';
    } else {
        // Check credentials
        $stmt = $db->prepare("SELECT * FROM admin WHERE email = :email LIMIT 1");
        $stmt->bindValue(':email', $email, SQLITE3_TEXT);
        $result = $stmt->execute();
        $admin = $result->fetchArray(SQLITE3_ASSOC);
        
        if($admin && password_verify($password, $admin['password'])) {
            // Successful login
            $_SESSION['admin'] = true;
            $_SESSION['admin_id'] = $admin['id'];
            
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Invalid email or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($site_title); ?> Admin - Login</title>
    
    <!-- Skeleton CSS -->
    <link rel="stylesheet" href="../assets/css/normalize.css">
    <link rel="stylesheet" href="../assets/css/skeleton.css">
    
    <!-- Custom Admin CSS -->
    <style>
        body {
            background-color: #f9f9f9;
            font-family: 'Hind Siliguri', sans-serif;
        }
        .admin-login {
            max-width: 400px;
            margin: 100px auto;
            padding: 30px;
            background: white;
            border: 1px solid #e1e1e1;
            border-radius: 4px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .admin-login h2 {
            margin-bottom: 30px;
            text-align: center;
        }
        .error-message {
            color: #e74c3c;
            margin-bottom: 20px;
        }
        .form-footer {
            margin-top: 20px;
            text-align: center;
        }
        .form-footer a {
            color: #777;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="admin-login">
            <h2>TinyBlog Admin</h2>
            
            <?php if(!empty($error)): ?>
                <div class="error-message">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="row">
                    <div class="twelve columns">
                        <label for="email">Email</label>
                        <input class="u-full-width" type="email" id="email" name="email" required>
                    </div>
                </div>
                
                <div class="row">
                    <div class="twelve columns">
                        <label for="password">Password</label>
                        <input class="u-full-width" type="password" id="password" name="password" required>
                    </div>
                </div>
                
                <button type="submit" class="button-primary u-full-width">Login</button>
                
                <div class="form-footer">
                    <a href="../index.php">Back to Blog</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>