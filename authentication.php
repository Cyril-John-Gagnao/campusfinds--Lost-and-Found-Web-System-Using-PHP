<?php
session_start();

function requireLogin() {
    // Check if either a user OR an admin is logged in
    if (!isset($_SESSION['user_ID'])) {
        // Clear any existing admin session to prevent cross-access
        if (isset($_SESSION['admin_ID'])) {
            session_destroy();
            session_start();
        }
        
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        $_SESSION['access_denied_message'] = "You must be logged in to access this page.";
        header('Location: ../index.php');
        exit();
    }
}

function requireAdminLogin() {
    // Check specifically for admin login
    if (!isset($_SESSION['admin_ID'])) {
        // Clear any existing user session to prevent cross-access
        if (isset($_SESSION['user_ID'])) {
            session_destroy();
            session_start();
        }
        
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        $_SESSION['access_denied_message'] = "You must be logged in as an administrator to access this page.";
        header('Location: ../index.php');
        exit();
    }
}

?>