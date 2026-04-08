<?php
require_once '../core/config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'teacher') { header("Location: ../index.php"); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $teacher_id = $_SESSION['user_id'];
    $student_id = (int)$_POST['student_id'];
    $class_id = (int)$_POST['class_id'];
    
    // Verify ownership indirectly
    $chk = $conn->query("SELECT id FROM classes WHERE id = $class_id AND teacher_id = $teacher_id");
    
    if($chk && $chk->num_rows > 0) {
        // DELETE PERMANENTLY FROM SYSTEM
        $conn->query("DELETE FROM users WHERE id = $student_id");
        $_SESSION['success'] = "Siswa dan seluruh rekam database-nya telah musnah secara mutlak.";
    } else {
        $_SESSION['error'] = "Akses terlarang.";
    }
}
header("Location: ../pages/manage_students.php"); exit;
?>
