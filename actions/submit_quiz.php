<?php
require_once '../core/config.php';
$r = isset($_SESSION['user_role']) ? trim(strtolower((string)$_SESSION['user_role'])) : '';
if (!isset($_SESSION['user_id']) || $r !== 'student') { header("Location: ../index.php"); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lesson_id = (int)$_POST['lesson_id'];
    $course_id = (int)$_POST['course_id'];
    $student_id = $_SESSION['user_id'];
    $answers = $_POST['answers']; // array of [question_id => option_id]
    
    // Prevent double submission
    $check = $conn->query("SELECT id FROM quiz_attempts WHERE lesson_id = $lesson_id AND student_id = $student_id");
    if($check && $check->num_rows > 0) {
        header("Location: ../pages/quiz_take.php?lesson_id=$lesson_id"); exit;
    }
    
    // Score Calculation
    $total_q_res = $conn->query("SELECT COUNT(id) as c FROM quiz_questions WHERE lesson_id = $lesson_id");
    $total_q = ($total_q_res && $total_q_res->num_rows > 0) ? (int)$total_q_res->fetch_assoc()['c'] : 0;
    
    $correct_count = 0;
    
    if($total_q > 0) {
        if(is_array($answers)) {
            foreach($answers as $qid => $opt_id) {
                $q = (int)$qid;
                $o = (int)$opt_id;
                // Verify if option is correct
                $res = $conn->query("SELECT is_correct FROM quiz_options WHERE id = $o");
                if($res && $res->num_rows > 0) {
                    $is_cor = (int)$res->fetch_assoc()['is_correct'];
                    if($is_cor === 1) {
                        $correct_count++;
                    }
                }
            }
        }
        $score = round(($correct_count / $total_q) * 100);
    } else {
        $score = 0;
    }
    
    // Insert attempt
    $conn->query("INSERT INTO quiz_attempts (student_id, lesson_id, score) VALUES ($student_id, $lesson_id, $score)");
    
    // Regardless of score, mark the lesson as completed to unlock next sequential lesson
    $prog_check = $conn->query("SELECT id FROM user_progress WHERE student_id = $student_id AND lesson_id = $lesson_id");
    if ($prog_check->num_rows == 0) {
        $conn->query("INSERT INTO user_progress (student_id, lesson_id) VALUES ($student_id, $lesson_id)");
    }
    
    $_SESSION['success'] = "Ujian telah selesai. Nilai Anda: $score / 100.";
    header("Location: ../pages/quiz_take.php?lesson_id=$lesson_id"); exit;
}
header("Location: ../pages/student_dashboard.php"); exit;
?>
