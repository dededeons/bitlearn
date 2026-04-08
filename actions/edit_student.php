<?php
require_once '../core/config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'teacher') { header("Location: ../index.php"); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $teacher_id = $_SESSION['user_id'];
    $student_id = (int)$_POST['student_id'];
    $old_class_id = (int)$_POST['old_class_id'];
    
    // Verify teacher owns the OLD class
    $chk1 = $conn->query("SELECT id FROM classes WHERE id = $old_class_id AND teacher_id = $teacher_id");
    if($chk1 && $chk1->num_rows > 0) {
        $name = $conn->real_escape_string(trim($_POST['name']));
        $username = $conn->real_escape_string(trim($_POST['email'])); // Maps to NISN input
        $new_class_id = (int)$_POST['new_class_id'];
        
        // Ensure new class is also owned by this teacher
        $chk2 = $conn->query("SELECT id FROM classes WHERE id = $new_class_id AND teacher_id = $teacher_id");
        if($chk2 && $chk2->num_rows > 0) {
            
            // Update User Meta
            $pwd_str = "";
            if(!empty($_POST['password'])) {
                $raw_p = $conn->real_escape_string($_POST['password']);
                $p = password_hash($raw_p, PASSWORD_DEFAULT);
                $pwd_str = ", password = '$p', temp_password = '$raw_p'";
            }
            $conn->query("UPDATE users SET name = '$name', username = '$username' $pwd_str WHERE id = $student_id AND role = 'student'");
            
            // Move Rombel Connection
            if($old_class_id != $new_class_id) {
                // Delete old and insert new (Avoid Unique Constraint conflict by doing it this way)
                $conn->query("DELETE FROM class_students WHERE student_id = $student_id AND class_id = $old_class_id");
                $conn->query("INSERT IGNORE INTO class_students (class_id, student_id) VALUES ($new_class_id, $student_id)");
            }
            
            $_SESSION['success'] = "Sempurna! Profil referensi Siswa tersimpan.";
        } else {
            $_SESSION['error'] = "Target class tidak valid.";
        }
    } else {
        $_SESSION['error'] = "Akses terlarang.";
    }
}
header("Location: ../pages/manage_students.php"); exit;
?>
