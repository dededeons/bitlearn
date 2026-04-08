<?php
require_once '../core/config.php';

// Only teachers can add students
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'teacher') {
    header("Location: ../index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $conn->real_escape_string(trim($_POST['name']));
    $email = $conn->real_escape_string(trim($_POST['email']));
    $password = $_POST['password'];

    if (empty($name) || empty($email) || empty($password)) {
        $_SESSION['error'] = 'All fields are required.';
        header("Location: ../pages/teacher_dashboard.php");
        exit;
    }

    // Check if email already exists
    $check_email = $conn->query("SELECT id FROM users WHERE email = '$email'");
    if ($check_email && $check_email->num_rows > 0) {
        $_SESSION['error'] = 'A user with this email already exists.';
        header("Location: ../pages/teacher_dashboard.php");
        exit;
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    $sql = "INSERT INTO users (name, email, password, role) VALUES ('$name', '$email', '$hashed_password', 'student')";
    
    if ($conn->query($sql) === TRUE) {
        $_SESSION['success'] = "Student <b>" . htmlspecialchars($name) . "</b> has been added successfully.";
    } else {
        $_SESSION['error'] = 'Error adding student: ' . $conn->error;
    }
}

header("Location: ../pages/teacher_dashboard.php");
exit;
?>
