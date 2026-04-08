<?php
require_once '../core/config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'teacher') { header("Location: ../index.php"); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = (int)$_POST['student_id'];
    $class_id = (int)$_POST['class_id'];
    
    // Verify ownership by checking if teacher owns this class
    $teacher_id = $_SESSION['user_id'];
    $check = $conn->query("SELECT id FROM classes WHERE id = $class_id AND teacher_id = $teacher_id");
    if($check && $check->num_rows > 0) {
        $conn->query("DELETE FROM class_students WHERE student_id = $student_id AND class_id = $class_id");
        $_SESSION['success'] = "Selesai! Siswa dikeluarkan dari rombel.";
    } else {
        $_SESSION['error'] = "Akses terlarang.";
    }
}
header("Location: ../pages/manage_students.php"); exit;
?>
