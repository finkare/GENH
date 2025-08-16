<?php
require_once 'config.php'; // Ensures session is started before we can destroy it.

// --- Session Destruction ---
// 1. Unset all session variables.
$_SESSION = [];

// 2. Delete the session cookie. This is a security best practice.
//    It forces the browser to forget the session ID.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. Finally, destroy the session data on the server.
session_destroy();

// --- Redirect ---
// Redirect the user to the homepage after logging out.
header('Location: index.php');
exit();
?>
