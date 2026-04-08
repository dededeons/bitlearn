<?php
require_once '../core/config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'student') { header("Location: ../index.php"); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_id = (int)$_POST['course_id'];
    $student_id = $_SESSION['user_id'];

    $check = $conn->query("SELECT id FROM enrollments WHERE course_id = $course_id AND student_id = $student_id");
    if($check->num_rows == 0) {
        $conn->query("INSERT INTO enrollments (course_id, student_id) VALUES ($course_id, $student_id)");
        $_SESSION['success'] = "Successfully enrolled in the course!";
    }
}
header("Location: ../pages/student_dashboard.php"); exit;
?>
