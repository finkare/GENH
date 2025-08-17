<?php
require_once __DIR__ . '/../config.php';

// This script handles logging out the administrator.

// Unset only the admin-specific session variables to avoid logging out
// a user who might also be logged into the main site.
unset($_SESSION['admin_user_id']);
unset($_SESSION['admin_full_name']);

// Redirect to the admin login page.
header('Location: login.php');
exit();
?>
