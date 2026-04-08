<?php
require_once '../core/config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'student') { header("Location: ../index.php"); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = $conn->real_escape_string(trim($_POST['enrollment_code']));
    $student_id = $_SESSION['user_id'];

    $result = $conn->query("SELECT id FROM courses WHERE enrollment_code = '$code'");
    
    if($result && $result->num_rows > 0) {
        $course_id = $result->fetch_assoc()['id'];
        
        $check = $conn->query("SELECT id FROM enrollments WHERE course_id = $course_id AND student_id = $student_id");
        if($check->num_rows == 0) {
            $conn->query("INSERT INTO enrollments (course_id, student_id) VALUES ($course_id, $student_id)");
            $_SESSION['success'] = "Berhasil bergabung ke mata pelajaran!";
        } else {
            $_SESSION['error'] = "Anda sudah tergabung dalam pelajaran ini.";
        }
    } else {
        $_SESSION['error'] = "Kode pelajaran tidak valid atau tidak ditemukan.";
    }
}
header("Location: ../pages/student_dashboard.php"); exit;
?>
