<?php
require_once '../core/config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'teacher') { header("Location: ../index.php"); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $assignment_id = (int)$_POST['assignment_id'];
    $course_id = (int)$_POST['course_id'];
    $teacher_id = $_SESSION['user_id'];
    
    // Verify ownership
    $chk = $conn->query("SELECT a.id FROM assignments a JOIN courses c ON a.course_id = c.id WHERE a.id = $assignment_id AND c.teacher_id = $teacher_id");
    if(!$chk || $chk->num_rows === 0) {
        $_SESSION['error'] = 'Akses ditolak.';
        header("Location: ../pages/manage_courses.php"); exit;
    }

    $title = $conn->real_escape_string(trim($_POST['title']));
    $description = $conn->real_escape_string(trim($_POST['description']));
    $due_date = $conn->real_escape_string($_POST['due_date']);
    $is_prereq = empty($_POST['is_prerequisite_of']) ? "NULL" : (int)$_POST['is_prerequisite_of'];
    
    if (empty($title) || empty($due_date)) {
        $_SESSION['error'] = 'Judul dan Tanggal Wajib diisi.';
        header("Location: ../pages/edit_assignment.php?id=$assignment_id"); exit;
    }

    $file_update_sql = "";
    if (isset($_FILES['attachment_file']) && $_FILES['attachment_file']['error'] === 0) {
        $upload_dir = '../uploads/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        
        $filename = time() . '_' . str_replace(' ', '_', $_FILES['attachment_file']['name']);
        if (move_uploaded_file($_FILES['attachment_file']['tmp_name'], $upload_dir . $filename)) {
            $file_path = $conn->real_escape_string($filename);
            $file_update_sql = ", file_path = '$file_path'";
        }
    }

    $is_published = isset($_POST['is_published']) ? (int)$_POST['is_published'] : 0;
    $sql = "UPDATE assignments SET title = '$title', description = '$description', due_date = '$due_date', is_prerequisite_of = $is_prereq, is_published = $is_published $file_update_sql WHERE id = $assignment_id";

    if ($conn->query($sql) === TRUE) {
        $_SESSION['success'] = 'Penugasan telah diperbarui!';
    } else {
        $_SESSION['error'] = 'Terjadi malfungsi Query: ' . $conn->error;
    }
}

header("Location: ../pages/course_view.php?id=$course_id"); exit;
?>
