<?php
require_once '../core/config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'teacher') { header("Location: ../index.php"); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $teacher_id = $_SESSION['user_id'];
    $class_id = (int)$_POST['class_id'];
    
    // Verify ownership
    $check = $conn->query("SELECT id FROM classes WHERE id = $class_id AND teacher_id = $teacher_id");
    if($check && $check->num_rows > 0) {
        $name = $conn->real_escape_string(trim($_POST['class_name']));
        $conn->query("UPDATE classes SET name = '$name' WHERE id = $class_id");
        $_SESSION['success'] = "Nama Rombel berhasil diubah.";
    } else {
        $_SESSION['error'] = "Akses ditolak!";
    }
}
header("Location: ../pages/manage_classes.php"); exit;
?>
