<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/database.php';

// Ensure the user is a logged-in admin.
if (!isset($_SESSION['admin_user_id'])) {
    // If not, send them to the admin login page.
    header('Location: login.php');
    exit();
}

// This script should only be accessed via a POST request.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: manage_users.php');
    exit();
}

// Get the user ID from the form submission and validate it.
$user_id = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
if (!$user_id) {
    $_SESSION['flash_message'] = "Invalid request: No user ID provided.";
    header('Location: manage_users.php');
    exit();
}

// To prevent an admin from deactivating their own account via this script,
// you might add a check here.
if ($user_id == $_SESSION['admin_user_id']) {
    $_SESSION['flash_message'] = "Error: You cannot change your own activation status.";
    header('Location: manage_users.php');
    exit();
}

// Fetch the user's current `is_active` status from the database.
$stmt = $mysqli->prepare("SELECT is_active FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    $_SESSION['flash_message'] = "Error: User not found.";
    header('Location: manage_users.php');
    exit();
}

// Determine the new status by toggling the current one.
$new_status = $user['is_active'] ? 0 : 1;
$status_text = $new_status ? "activated" : "deactivated";

// Prepare and execute the UPDATE statement.
$update_stmt = $mysqli->prepare("UPDATE users SET is_active = ? WHERE id = ?");
$update_stmt->bind_param("ii", $new_status, $user_id);

if ($update_stmt->execute()) {
    // Set a success message.
    $_SESSION['flash_message'] = "User successfully {$status_text}.";
} else {
    // Set an error message.
    $_SESSION['flash_message'] = "Database Error: Could not update user status.";
}

$update_stmt->close();
$mysqli->close();

// Redirect back to the user management page.
header('Location: manage_users.php');
exit();
?>
