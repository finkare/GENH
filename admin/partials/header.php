<?php
require_once __DIR__ . '/../../config.php';

// Admin area protection:
// Check if the admin session variable is set.
// If not, redirect to the login page.
if (!isset($_SESSION['admin_user_id'])) {
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Flix9 Hub</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header class="admin-header">
        <div class="header-container">
            <h1>Flix9 Hub Admin</h1>
            <nav>
                <a href="index.php">Dashboard</a>
                <a href="manage_projects.php">Manage Projects</a>
                <a href="#">Manage Users</a>
                <a href="../index.php" target="_blank">View Site</a>
                <a href="logout.php">Logout</a>
            </nav>
        </div>
    </header>
    <main class="admin-wrapper">
        <div class="content-container">
