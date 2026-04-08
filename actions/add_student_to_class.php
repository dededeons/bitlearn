<?php
require_once '../core/config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'teacher') { header("Location: ../index.php"); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $class_id = (int)$_POST['class_id'];
    $name = $conn->real_escape_string(trim($_POST['name']));
    $username = $conn->real_escape_string(trim($_POST['username'])); // previously email
    
    // Generate 6 random char password
    $raw_password = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 6);
    $hashed_password = password_hash($raw_password, PASSWORD_DEFAULT);

    // Validate username (NISN)
    $check = $conn->query("SELECT id FROM users WHERE username = '$username'");
    if ($check && $check->num_rows > 0) {
        $student_id = $check->fetch_assoc()['id'];
        // Just link to class
    } else {
        $conn->query("INSERT INTO users (name, username, password, temp_password, role) VALUES ('$name', '$username', '$hashed_password', '$raw_password', 'student')");
        $student_id = $conn->insert_id;
    }

    // Assign to class
    $chk_link = $conn->query("SELECT student_id FROM class_students WHERE class_id = $class_id AND student_id = $student_id");
    if ($chk_link->num_rows == 0) {
        $conn->query("INSERT INTO class_students (class_id, student_id) VALUES ($class_id, $student_id)");
        $_SESSION['success'] = "Siswa berhasil didaftarkan ke Rombel dengan Sandi Acak.";
    } else {
        $_SESSION['error'] = "Siswa sudah terdaftar di Rombel ini.";
    }
}
$return_url = isset($_POST['return_url']) ? $_POST['return_url'] : "../pages/manage_classes.php";
header("Location: " . $return_url);
exit;
?>
