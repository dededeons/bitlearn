<?php
require_once '../core/config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'teacher') { header("Location: ../index.php"); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lesson_id = (int)$_POST['lesson_id'];
    $course_id = (int)$_POST['course_id']; // for routing back
    $qtext = $conn->real_escape_string(trim($_POST['question_text']));
    $options = $_POST['options']; // Array of 4 strings
    $correct_idx = (int)$_POST['correct_option']; // 0, 1, 2, or 3
    
    // Insert Question
    $conn->query("INSERT INTO quiz_questions (lesson_id, question_text) VALUES ($lesson_id, '$qtext')");
    $qid = $conn->insert_id;
    
    // Insert 4 Options
    foreach($options as $i => $opt_text) {
        $clean_opt = $conn->real_escape_string(trim($opt_text));
        $is_cor = ($i === $correct_idx) ? 1 : 0;
        $conn->query("INSERT INTO quiz_options (question_id, option_text, is_correct) VALUES ($qid, '$clean_opt', $is_cor)");
    }
    
    $_SESSION['success'] = "Satu Butir Soal berhasil dimasukkan ke Kuis!";
    header("Location: ../pages/builder_quiz.php?lesson_id=$lesson_id&course_id=$course_id"); exit;
}
header("Location: ../pages/teacher_dashboard.php"); exit;
?>
