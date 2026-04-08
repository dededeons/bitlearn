<?php
require_once '../core/config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'teacher') { header("Location: ../index.php"); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $conn->real_escape_string(trim($_POST['title']));
    $description = $conn->real_escape_string(trim($_POST['description']));
    $enrollment_code = trim($_POST['enrollment_code']);
    
    // Process image file upload
    $thumbnail_url = '';
    if (isset($_FILES['thumbnail_file']) && $_FILES['thumbnail_file']['error'] === 0) {
        $upload_dir = '../uploads/thumbnails/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        
        $filename = time() . '_' . str_replace(' ', '_', $_FILES['thumbnail_file']['name']);
        if (move_uploaded_file($_FILES['thumbnail_file']['tmp_name'], $upload_dir . $filename)) {
            $thumbnail_url = $conn->real_escape_string($filename);
        }
    }
    
    // check if enrollment code is empty, we force NULL so UNIQUE constraint doesn't throw on empty string
    $code_val = empty($enrollment_code) ? "NULL" : "'" . $conn->real_escape_string($enrollment_code) . "'";
    $teacher_id = $_SESSION['user_id'];

    if (empty($title)) {
        $_SESSION['error'] = 'Judul pelajaran wajib diisi.';
        header("Location: ../pages/manage_courses.php"); exit;
    }

    $sql = "INSERT INTO courses (title, description, thumbnail_url, teacher_id, enrollment_code) VALUES ('$title', '$description', '$thumbnail_url', $teacher_id, $code_val)";
    if ($conn->query($sql) === TRUE) {
        $course_id = $conn->insert_id;
        $_SESSION['success'] = 'Course berhasil dibuat!';
        
        // Handle Rombel linking
        if(isset($_POST['allowed_classes']) && is_array($_POST['allowed_classes'])) {
            foreach($_POST['allowed_classes'] as $cls_id) {
                $c_id = (int)$cls_id;
                $conn->query("INSERT INTO course_classes (course_id, class_id) VALUES ($course_id, $c_id)");
            }
        }
    } else {
        $_SESSION['error'] = 'Gagal membuat pelajaran. Pastikan Kode Pendaftaran yang dimasukan UNIK. ' . $conn->error;
    }
}
header("Location: ../pages/manage_courses.php"); exit;
?>
