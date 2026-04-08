<?php
session_start();

// Database configuration
$db_host = 'localhost';
$db_user = 'root'; // default xampp user
$db_pass = '';     // default xampp pass is empty
$db_name = 'bitlearn_db';

// Create connection
$conn = new mysqli($db_host, $db_user, $db_pass);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Select the database if it exists
if ($conn->select_db($db_name) === false) {
    // Database doesn't exist yet, we don't throw an error here 
    // because install.php needs to run first.
} else {
    // Check if configuration needs to enforce utf8 encoding
    $conn->set_charset("utf8mb4");
}

// Base URL configuration for easier routing
define('BASE_URL', 'http://localhost/BitLearn');
?>
