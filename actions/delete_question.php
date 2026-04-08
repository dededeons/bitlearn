<?php
require_once '../core/config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'teacher') { header("Location: ../index.php"); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $qid = (int)$_POST['question_id'];
    $lesson_id = (int)$_POST['lesson_id'];
    $course_id = (int)$_POST['course_id'];
    $conn->query("DELETE FROM quiz_questions WHERE id = $qid");
    $_SESSION['success'] = "Soal berhasil dibuang.";
    header("Location: ../pages/builder_quiz.php?lesson_id=$lesson_id&course_id=$course_id"); exit;
}
?>
