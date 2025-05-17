<?php
// admin/functions.php - Admin specific functions

// Change admin credentials
function change_admin_credentials($db, $new_email, $new_password) {
    // Validate inputs
    if(empty($new_email) || empty($new_password)) {
        return 'Email and password cannot be empty.';
    }
    
    // Validate email format
    if(!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        return 'Invalid email format.';
    }
    
    // Validate password length
    if(strlen($new_password) < 8) {
        return 'Password must be at least 8 characters long.';
    }
    
    // Hash new password
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    
    // Update admin credentials
    $stmt = $db->prepare("UPDATE admin SET email = :email, password = :password WHERE id = 1");
    $stmt->bindValue(':email', $new_email, SQLITE3_TEXT);
    $stmt->bindValue(':password', $hashed_password, SQLITE3_TEXT);
    
    if($stmt->execute()) {
        return true;
    } else {
        return 'Failed to update admin credentials.';
    }
}