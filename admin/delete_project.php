<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/database.php';

// Check if user is an admin by checking the admin session variable.
if (!isset($_SESSION['admin_user_id'])) {
    header('Location: login.php');
    exit();
}

// Get the project ID from the URL and validate it.
$project_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$project_id) {
    // Redirect if no valid ID is provided.
    header('Location: manage_projects.php');
    exit();
}

// Before deleting, you might want to add CSRF token validation in a real application.
// For example: if (!hash_equals($_SESSION['csrf_token'], $_GET['token'])) { die('Invalid CSRF token'); }

// Prepare and execute the DELETE statement.
$stmt = $mysqli->prepare("DELETE FROM projects WHERE id = ?");
$stmt->bind_param("i", $project_id);

if ($stmt->execute()) {
    // Success: Set a success flash message.
    $_SESSION['flash_message'] = "Project deleted successfully.";
} else {
    // Failure: Set an error flash message.
    $_SESSION['flash_message'] = "Error: Could not delete project. It might be in use.";
    // For debugging: error_log('Delete project error: ' . $stmt->error);
}

$stmt->close();
$mysqli->close();

// Redirect back to the projects list page.
header('Location: manage_projects.php');
exit();
?>
