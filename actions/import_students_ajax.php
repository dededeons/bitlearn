<?php
require_once '../core/config.php';

// Cek apakah request valid dan user adalah guru
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'teacher') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$teacher_id = $_SESSION['user_id'];

// Ambil payload JSON dari Javascript
$json_data = file_get_contents('php://input');
$payload = json_decode($json_data, true);

if (!isset($payload['students']) || !is_array($payload['students'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Payload JSON tidak valid!']);
    exit;
}

$students = $payload['students'];
$success_count = 0;
$skipped_count = 0;
$rombel_not_found = 0;

// Mulai pemrosesan data (Batch Insert)
foreach ($students as $row) {
    if (empty($row['Nama_Lengkap']) || empty($row['NISN_Username']) || empty($row['Nama_Rombel_Tujuan'])) {
        $skipped_count++;
        continue;
    }

    $name = $conn->real_escape_string(trim($row['Nama_Lengkap']));
    $username = $conn->real_escape_string(trim(preg_replace('/\s+/', '', $row['NISN_Username']))); // Hilangkan spasi di username
    $rombel_name = $conn->real_escape_string(trim($row['Nama_Rombel_Tujuan']));

    // Apakah password diisi di Excel? Jika tidak, buatkan acak 6 karakter
    $raw_password = '';
    if (!empty($row['Password_Opsional'])) {
        $raw_password = trim($row['Password_Opsional']);
    } else {
        $raw_password = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 6);
    }
    $hashed_password = password_hash($raw_password, PASSWORD_DEFAULT);

    // 1. Cek apakah Rombel dengan nama tersebut ada TERDAFTAR ATAS NAMA GURU INI
    $check_class = $conn->query("SELECT id FROM classes WHERE name = '$rombel_name' AND teacher_id = $teacher_id");
    if ($check_class && $check_class->num_rows > 0) {
        $class_id = $check_class->fetch_assoc()['id'];

        // 2. Cek apakah Username (NISN) tersebut sudah pernah terdaftar di tabel users?
        $check_user = $conn->query("SELECT id FROM users WHERE username = '$username'");
        if ($check_user && $check_user->num_rows > 0) {
            $skipped_count++; // Duplikat Username!
            continue;
        }

        // 3. Insert ke dalam users (Role: Student)
        $insert_user_sql = "INSERT INTO users (username, password, name, role, temp_password) VALUES ('$username', '$hashed_password', '$name', 'student', '$raw_password')";
        if ($conn->query($insert_user_sql) === TRUE) {
            $new_student_id = $conn->insert_id;

            // 4. Masukkan siswa ke dalam tabel class_students agar otomatis gabung ke Rombel
            $conn->query("INSERT INTO class_students (class_id, student_id) VALUES ($class_id, $new_student_id)");
            
            $success_count++;
        } else {
            $skipped_count++; // Gagal mengeksekusi insert
        }

    } else {
        $rombel_not_found++; // Kelas tidak ditemukan atau bukan milik guru ini
    }
}

// Berikan respon detail ke pemanggil Javascript
echo json_encode([
    'status' => 'success',
    'message' => "Pengolahan Selesai! $success_count Siswa Berhasil Ditambahkan.",
    'details' => [
        'success' => $success_count,
        'skipped_username_duplicate' => $skipped_count,
        'skipped_rombel_not_found' => $rombel_not_found
    ]
]);
exit;
?>
