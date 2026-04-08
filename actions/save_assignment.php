<?php
require_once '../core/config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'teacher') { header("Location: ../index.php"); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_id = (int)$_POST['course_id'];
    $title = $conn->real_escape_string(trim($_POST['title']));
    $description = $conn->real_escape_string(trim($_POST['description']));
    $due_date = $conn->real_escape_string($_POST['due_date']);
    $is_prereq = empty($_POST['is_prerequisite_of']) ? "NULL" : (int)$_POST['is_prerequisite_of'];
    
    if (empty($title) || empty($due_date)) {
        $_SESSION['error'] = 'Judul dan Tanggal Wajib diisi.';
        header("Location: ../pages/add_assignment.php?course_id=$course_id"); exit;
    }

    $file_path = NULL;
    if (isset($_FILES['attachment_file']) && $_FILES['attachment_file']['error'] === 0) {
        $upload_dir = '../uploads/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        
        $filename = time() . '_' . str_replace(' ', '_', $_FILES['attachment_file']['name']);
        if (move_uploaded_file($_FILES['attachment_file']['tmp_name'], $upload_dir . $filename)) {
            $file_path = $conn->real_escape_string($filename);
        }
    }

    $is_published = isset($_POST['is_published']) ? (int)$_POST['is_published'] : 1;

    if ($file_path) {
        $sql = "INSERT INTO assignments (course_id, title, description, due_date, file_path, is_prerequisite_of, is_published) VALUES ($course_id, '$title', '$description', '$due_date', '$file_path', $is_prereq, $is_published)";
    } else {
        $sql = "INSERT INTO assignments (course_id, title, description, due_date, is_prerequisite_of, is_published) VALUES ($course_id, '$title', '$description', '$due_date', $is_prereq, $is_published)";
    }

    if ($conn->query($sql) === TRUE) {
        $_SESSION['success'] = 'Penugasan telah dijadwalkan!';
    } else {
        $_SESSION['error'] = 'Terjadi malfungsi Query: ' . $conn->error;
    }
}

header("Location: ../pages/course_view.php?id=$course_id"); exit;
?>
