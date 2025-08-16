<?php

// Include the configuration file
// Using __DIR__ ensures the path is always correct, regardless of where this file is included from.
require_once __DIR__ . '/../config.php';

/**
 * Establish a database connection using MySQLi.
 * The connection object is stored in the $mysqli variable.
 * The script will terminate with an error message if the connection fails.
 */

// Create a new MySQLi object for the database connection
$mysqli = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check for connection errors
if ($mysqli->connect_error) {
    // In a production environment, you would log this error and show a more generic message.
    // For development purposes, showing the actual error is helpful for debugging.
    error_log('Database Connection Failed: ' . $mysqli->connect_error);
    die('Sorry, we are experiencing some technical difficulties. Please try again later.');
}

// Set the character set to utf8mb4 to ensure proper handling of all characters, including emojis.
if (!$mysqli->set_charset('utf8mb4')) {
    error_log('Error loading character set utf8mb4: ' . $mysqli->error);
    // Continue execution, but log the error.
}

// The $mysqli object is now available for use in any script that includes this file.

?>
