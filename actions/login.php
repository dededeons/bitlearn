<?php
require_once '../core/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $conn->real_escape_string(trim($_POST['email']));
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $_SESSION['error'] = 'Harap isi username dan password.';
        header("Location: ../index.php");
        exit;
    }

    $sql = "SELECT id, name, username, password, role, profile_pic FROM users WHERE username = '$username'";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Verify password
        if (password_verify($password, $user['password'])) {
            // Setup Session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_username'] = $user['username'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['profile_pic'] = $user['profile_pic'];

            // Redirect based on role
            if ($user['role'] === 'teacher') {
                header("Location: ../pages/teacher_dashboard.php");
            } else {
                header("Location: ../pages/student_dashboard.php");
            }
            exit;
        } else {
            $_SESSION['error'] = 'Kredensial username atau kata sandi yang Anda masukkan salah.';
            header("Location: ../index.php");
            exit;
        }
    } else {
        $_SESSION['error'] = 'Kredensial username atau kata sandi yang Anda masukkan salah.';
        header("Location: ../index.php");
        exit;
    }
} else {
    header("Location: ../index.php");
    exit;
}
?>