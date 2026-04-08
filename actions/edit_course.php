<?php
require_once '../core/config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'teacher') { header("Location: ../index.php"); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $teacher_id = $_SESSION['user_id'];
    $course_id = (int)$_POST['course_id'];
    
    // Check ownership
    $check = $conn->query("SELECT id FROM courses WHERE id = $course_id AND teacher_id = $teacher_id");
    if($check && $check->num_rows > 0) {
        $title = $conn->real_escape_string(trim($_POST['title']));
        $description = $conn->real_escape_string(trim($_POST['description']));
        $enrollment_code = trim($_POST['enrollment_code']);
        $code_val = empty($enrollment_code) ? "NULL" : "'" . $conn->real_escape_string($enrollment_code) . "'";
        
        $thumbnail_query_part = '';
        if (isset($_FILES['thumbnail_file']) && $_FILES['thumbnail_file']['error'] === 0) {
            $upload_dir = '../uploads/thumbnails/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
            
            $filename = time() . '_' . str_replace(' ', '_', $_FILES['thumbnail_file']['name']);
            if (move_uploaded_file($_FILES['thumbnail_file']['tmp_name'], $upload_dir . $filename)) {
                $thumbnail_url = $conn->real_escape_string($filename);
                $thumbnail_query_part = ", thumbnail_url = '$thumbnail_url'";
            }
        }
        
        $sql = "UPDATE courses SET title = '$title', description = '$description', enrollment_code = $code_val $thumbnail_query_part WHERE id = $course_id";
        if($conn->query($sql) === TRUE) {
            $_SESSION['success'] = "Info Course berhasil disegarkan.";
        } else {
            $_SESSION['error'] = "Gagal memperbarui info Course: " . $conn->error;
        }
    } else {
        $_SESSION['error'] = "Akses terlarang.";
    }
}
header("Location: ../pages/manage_courses.php"); exit;
?>
