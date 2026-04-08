<?php
require_once '../core/config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'teacher') { header("Location: ../index.php"); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $teacher_id = $_SESSION['user_id'];
    $course_id = (int)$_POST['course_id'];
    
    // Check ownership
    $check = $conn->query("SELECT id FROM courses WHERE id = $course_id AND teacher_id = $teacher_id");
    if($check && $check->num_rows > 0) {
        // Safe to execute, cascading triggers will wipe out dependent table data (lessons, assignments, submissions)
        $conn->query("DELETE FROM courses WHERE id = $course_id");
        $_SESSION['success'] = "Satu Course dan seluruh datanya telah diremukkan jadi abu.";
    } else {
        $_SESSION['error'] = "Anda tidak berhak menghapus wilayah ini.";
    }
}
header("Location: ../pages/manage_courses.php"); exit;
?>
