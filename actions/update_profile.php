<?php
require_once '../core/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $name = $conn->real_escape_string(trim($_POST['name']));
    $username = $conn->real_escape_string(trim($_POST['username']));
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validasi
    if (empty($name) || empty($username)) {
        $_SESSION['error'] = "Nama Lengkap dan NIP tidak boleh kosong!";
        header("Location: ../pages/edit_profile.php");
        exit;
    }

    // Check username unique
    $check_username = $conn->query("SELECT id FROM users WHERE username = '$username' AND id != $user_id");
    if ($check_username->num_rows > 0) {
        $_SESSION['error'] = "Username/NIP tersebut sudah digunakan oleh akun lain.";
        header("Location: ../pages/edit_profile.php");
        exit;
    }

    $update_fields = [];
    $update_fields[] = "name = '$name'";
    $update_fields[] = "username = '$username'";

    // Handle Password
    if (!empty($new_password)) {
        if ($new_password !== $confirm_password) {
            $_SESSION['error'] = "Konfirmasi kata sandi baru tidak sama.";
            header("Location: ../pages/edit_profile.php");
            exit;
        }
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $update_fields[] = "password = '$hashed'";
    }

    // Handle Upload Foto
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['jpg', 'jpeg', 'png'];
        $filename = $_FILES['profile_pic']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (!in_array($ext, $allowed)) {
            $_SESSION['error'] = "Gagal: Format file foto harus JPG atau PNG.";
            header("Location: ../pages/edit_profile.php");
            exit;
        }

        if ($_FILES['profile_pic']['size'] > 2000000) { // 2MB
            $_SESSION['error'] = "Gagal: Ukuran pasfoto terlalu besar (Maksimal 2MB).";
            header("Location: ../pages/edit_profile.php");
            exit;
        }

        $new_filename = "avatar_" . $user_id . "_" . time() . "." . $ext;
        $dest_path = "../uploads/" . $new_filename;

        // Ensure directory exists
        if(!is_dir("../uploads/")){
            mkdir("../uploads/", 0777, true);
        }

        if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $dest_path)) {
            // Delete old picture logic (optional, but good for disk space)
            $old_q = $conn->query("SELECT profile_pic FROM users WHERE id = $user_id");
            $old = $old_q->fetch_assoc();
            if(!empty($old['profile_pic']) && file_exists("../uploads/" . $old['profile_pic'])) {
                unlink("../uploads/" . $old['profile_pic']);
            }
            
            $update_fields[] = "profile_pic = '$new_filename'";
            $_SESSION['profile_pic'] = $new_filename; // Cached in session for quick access
        } else {
            $_SESSION['error'] = "Gagal memproses unggahan fail gambar Anda.";
            header("Location: ../pages/edit_profile.php");
            exit;
        }
    }

    $set_clause = implode(", ", $update_fields);
    $query = "UPDATE users SET $set_clause WHERE id = $user_id";
    
    if ($conn->query($query)) {
        $_SESSION['user_name'] = $name; // Update session name
        $_SESSION['user_username'] = $username;
        $_SESSION['success'] = "Identitas dan Pasfoto Profil Anda berhasil diperbarui!";
    } else {
        $_SESSION['error'] = "Galat basis data: " . $conn->error;
    }
}

header("Location: ../pages/edit_profile.php");
exit;
?>
