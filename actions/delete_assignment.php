<?php
require_once '../core/config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'teacher') { header("Location: ../index.php"); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $teacher_id = $_SESSION['user_id'];
    $assignment_id = (int)$_POST['assignment_id'];
    $course_id = (int)$_POST['course_id'];
    
    // Verify ownership
    $chk = $conn->query("SELECT courses.id FROM assignments JOIN courses ON assignments.course_id = courses.id WHERE assignments.id = $assignment_id AND courses.teacher_id = $teacher_id");
    
    if($chk && $chk->num_rows > 0) {
        $conn->query("DELETE FROM assignments WHERE id = $assignment_id");
        $_SESSION['success'] = "Agenda penugasan telah dicabut.";
    } else {
        $_SESSION['error'] = "Akses terlarang.";
    }
}
header("Location: ../pages/course_view.php?id=" . $course_id); exit;
?>
