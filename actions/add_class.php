<?php
require_once '../core/config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'teacher') { header("Location: ../index.php"); exit; }

$name = $conn->real_escape_string(trim($_POST['class_name']));
$teacher_id = $_SESSION['user_id'];

if(!empty($name)) {
    $conn->query("INSERT INTO classes (name, teacher_id) VALUES ('$name', $teacher_id)");
    $_SESSION['success'] = "Rombel '$name' berhasil dibuat.";
}
header("Location: ../pages/manage_classes.php");
?>
