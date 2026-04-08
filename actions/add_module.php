<?php
require_once '../core/config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'teacher') { header("Location: ../index.php"); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $conn->real_escape_string(trim($_POST['title']));
    $course_id = (int)$_POST['course_id'];
    $teacher_id = $_SESSION['user_id'];

    if (empty($title)) {
        $_SESSION['error'] = 'Module title is required.';
        header("Location: ../pages/course_view.php?id=$course_id"); exit;
    }

    $is_published = isset($_POST['is_published']) ? (int)$_POST['is_published'] : 1;

    // verify teacher owns course
    $check = $conn->query("SELECT id FROM courses WHERE id = $course_id AND teacher_id = $teacher_id");
    if($check->num_rows > 0) {
        $sql = "INSERT INTO modules (course_id, title, is_published) VALUES ($course_id, '$title', $is_published)";
        if ($conn->query($sql) === TRUE) {
            $_SESSION['success'] = 'Module added successfully!';
        } else {
            $_SESSION['error'] = 'Error: ' . $conn->error;
        }
    }
}
header("Location: ../pages/course_view.php?id=$course_id"); exit;
?>
