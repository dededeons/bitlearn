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

// Dynamically determine BASE_URL
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$doc_root = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
$app_dir = str_replace('\\', '/', dirname(__DIR__));
$base_path = str_replace($doc_root, '', $app_dir);

// Handle edge cases where str_replace doesn't work perfectly due to symlinks/casing
if (strpos($app_dir, $doc_root) === false && isset($_SERVER['SCRIPT_NAME'])) {
     $script_dir = dirname($_SERVER['SCRIPT_NAME']);
     // Approximate base path by removing subdirectories if we are in one
     $script_file = basename($_SERVER['PHP_SELF']);
     if ($script_file === 'install.php' || $script_file === 'index.php') {
         $base_path = rtrim($script_dir, '/');
     } else {
         $base_path = rtrim(dirname($script_dir), '/'); // Works for 1 level deep like /pages
     }
}

define('BASE_URL', $protocol . '://' . $host . $base_path);

// Check if installation is complete
if (!file_exists(__DIR__ . '/installed.lock')) {
    if (basename($_SERVER['PHP_SELF']) !== 'install.php') {
        header("Location: " . BASE_URL . "/install.php");
        exit;
    }
} else {
    // Select the database if it exists
    if ($conn->select_db($db_name) === false) {
        die("Fatal Error: Database specified in config.php does not exist although installation is marked complete. Please delete core/installed.lock and re-run installer.");
    } else {
        $conn->set_charset("utf8mb4");
    }
}
?>
