<?php
require_once '../core/config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'teacher') { header("Location: ../index.php"); exit; }

$course_id = (int)$_POST['course_id'];
$title = $conn->real_escape_string(trim($_POST['title']));
$desc = $conn->real_escape_string(trim($_POST['description']));
$due_date = $conn->real_escape_string($_POST['due_date']);

$sql = "INSERT INTO assignments (course_id, title, description, due_date) VALUES ($course_id, '$title', '$desc', '$due_date')";
if ($conn->query($sql) === TRUE) {
    $_SESSION['success'] = "Assignment created successfully.";
} else {
    $_SESSION['error'] = "Error: " . $conn->error;
}
header("Location: ../pages/course_view.php?id=$course_id"); exit;
?>
