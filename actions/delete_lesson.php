<?php
require_once '../core/config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'teacher') { header("Location: ../index.php"); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $teacher_id = $_SESSION['user_id'];
    $lesson_id = (int)$_POST['lesson_id'];
    $course_id = (int)$_POST['course_id'];
    
    // Verify ownership indirectly
    $chk = $conn->query("SELECT courses.id FROM lessons JOIN modules ON lessons.module_id = modules.id JOIN courses ON modules.course_id = courses.id WHERE lessons.id = $lesson_id AND courses.teacher_id = $teacher_id");
    
    if($chk && $chk->num_rows > 0) {
        $conn->query("DELETE FROM lessons WHERE id = $lesson_id");
        $_SESSION['success'] = "Satu materi berhasil dimusnahkan.";
    } else {
        $_SESSION['error'] = "Akses terlarang.";
    }
}
header("Location: ../pages/course_view.php?id=" . $course_id); exit;
?>
