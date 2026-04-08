<?php
require_once '../core/config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'student') { header("Location: ../index.php"); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lesson_id = (int)$_POST['lesson_id'];
    $course_id = (int)$_POST['course_id'];
    $student_id = $_SESSION['user_id'];

    $check = $conn->query("SELECT id FROM user_progress WHERE student_id = $student_id AND lesson_id = $lesson_id");
    if($check->num_rows == 0) {
        $conn->query("INSERT INTO user_progress (student_id, lesson_id) VALUES ($student_id, $lesson_id)");
    }
    
    // Auto redirect
    if (isset($_POST['next_lesson_id']) && (int)$_POST['next_lesson_id'] > 0) {
        $next_id = (int)$_POST['next_lesson_id'];
        header("Location: ../pages/lesson_viewer.php?course_id=$course_id&lesson_id=$next_id");
        exit;
    }
    
    header("Location: ../pages/lesson_viewer.php?course_id=$course_id&lesson_id=$lesson_id"); exit;
}
header("Location: ../pages/student_dashboard.php"); exit;
?>
