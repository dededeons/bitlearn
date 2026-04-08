<?php
require_once '../core/config.php';

// Cek Otorisasi
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'teacher') {
    header("Location: ../index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $teacher_id = $_SESSION['user_id'];
    $module_id = (int)$_POST['module_id'];
    $course_id = (int)$_POST['course_id'];
    
    // Verifikasi Bahwa Modul Ini Benar Milik Guru Tersebut
    $check_query = "SELECT m.id FROM modules m 
                    JOIN courses c ON m.course_id = c.id 
                    WHERE m.id = $module_id AND c.teacher_id = $teacher_id LIMIT 1";
    $chk = $conn->query($check_query);
    
    if ($chk && $chk->num_rows > 0) {
        // Hapus struktur anak bawahan (Materi/Lesson) secara paksa agar tidak terjadi Error Foreign Key constraint
        $conn->query("DELETE FROM lessons WHERE module_id = $module_id");
        
        // Hancurkan struktur wadah Modul utama
        if ($conn->query("DELETE FROM modules WHERE id = $module_id")) {
            $_SESSION['success'] = "Satu Bab Modul beserta seluruh anak materinya telah rata dengan tanah.";
        } else {
            $_SESSION['error'] = "Terjadi galat pada sistem pangkalan data saat menghapus Modul.";
        }
    } else {
        $_SESSION['error'] = "Aksi ditolak. Modul ini bukan milik Anda atau sudah tidak ada.";
    }
}

// Kembalikan ke layar Silabus Course
header("Location: ../pages/course_view.php?id=" . $course_id);
exit;
?>
