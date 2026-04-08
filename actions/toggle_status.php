<?php
require_once '../core/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'teacher') {
    header("Location: ../index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type']; // 'module', 'lesson', or 'assignment'
    $id = (int)$_POST['id'];
    $course_id = (int)$_POST['course_id'];
    $teacher_id = $_SESSION['user_id'];

    // List of allowed tables
    $allowed_types = [
        'module' => 'modules',
        'lesson' => 'lessons',
        'assignment' => 'assignments'
    ];

    if (!array_key_exists($type, $allowed_types)) {
        $_SESSION['error'] = 'Unggah status gagal: Tipe tidak dikenal.';
        header("Location: ../pages/course_view.php?id=$course_id");
        exit;
    }

    $table = $allowed_types[$type];

    // Verify ownership
    $valid = false;
    if ($type === 'module') {
        $check = $conn->query("SELECT m.id FROM modules m JOIN courses c ON m.course_id = c.id WHERE m.id = $id AND c.teacher_id = $teacher_id");
        if ($check && $check->num_rows > 0) $valid = true;
    } elseif ($type === 'lesson') {
        $check = $conn->query("SELECT l.id FROM lessons l JOIN modules m ON l.module_id = m.id JOIN courses c ON m.course_id = c.id WHERE l.id = $id AND c.teacher_id = $teacher_id");
        if ($check && $check->num_rows > 0) $valid = true;
    } elseif ($type === 'assignment') {
        $check = $conn->query("SELECT a.id FROM assignments a JOIN courses c ON a.course_id = c.id WHERE a.id = $id AND c.teacher_id = $teacher_id");
        if ($check && $check->num_rows > 0) $valid = true;
    }

    if ($valid) {
        $conn->query("UPDATE $table SET is_published = 1 - is_published WHERE id = $id");
        $_SESSION['success'] = "Status visibilitas berhasil diubah.";
    } else {
        $_SESSION['error'] = "Akses ditolak.";
    }
}

header("Location: ../pages/course_view.php?id=$course_id");
exit;
