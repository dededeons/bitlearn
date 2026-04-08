<?php
require_once '../core/config.php';

// Memastikan Akses Validasi Admin Guru
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'teacher') {
    header("Location: ../index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $teacher_id = $_SESSION['user_id'];
    $module_id = (int)$_POST['module_id'];
    $course_id = (int)$_POST['course_id'];
    $new_title = $conn->real_escape_string(trim($_POST['new_title']));
    
    // Periksa apakah input judul bab nihil / blank
    if(empty($new_title)) {
        $_SESSION['error'] = "Tajuk Modul Pembelajaran tidak boleh kosong.";
        header("Location: ../pages/course_view.php?id=" . $course_id);
        exit;
    }

    // Verifikasi Bahwa Modul Asli Milik Guru yang Bersangkutan
    $chk_sql = "SELECT m.id FROM modules m 
                JOIN courses c ON m.course_id = c.id 
                WHERE m.id = $module_id AND c.teacher_id = $teacher_id LIMIT 1";
    $chk = $conn->query($chk_sql);
    
    if ($chk && $chk->num_rows > 0) {
        $is_published = isset($_POST['is_published']) ? (int)$_POST['is_published'] : 0;
        // Meluncurkan operasi Rename (UPDATE title)
        $update_sql = "UPDATE modules SET title = '$new_title', is_published = $is_published WHERE id = $module_id";
        
        if ($conn->query($update_sql)) {
            $_SESSION['success'] = "Sempurna! Judul Bab Konsep berhasil diperbarui menjadi '$new_title'.";
        } else {
            $_SESSION['error'] = "Wah, terjadi galat pada peladen saat menyunting modul.";
        }
    } else {
        $_SESSION['error'] = "Aksi terlarang. Modul ini di luar otorisasi hak milik Anda.";
    }
}

// Terbangkan Guru kembali ke Papan Tulis Kelas
header("Location: ../pages/course_view.php?id=" . $course_id);
exit;
?>
