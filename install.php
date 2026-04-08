<?php
require_once __DIR__ . '/core/config.php';

// Block if already installed via lock file
if (file_exists(__DIR__ . '/core/installed.lock')) {
    die("Aplikasi sudah diinstall. Untuk menginstall ulang, hapus file core/installed.lock terlebih dahulu.");
}

// Security: Block if database is already fully configured and 'users' table exists
if (isset($conn) && !$conn->connect_error && $conn->select_db($db_name)) {
    $result = $conn->query("SHOW TABLES LIKE 'users'");
    if ($result && $result->num_rows > 0) {
        die("Akses Ditolak: Database sudah terhubung dan aplikasi BitLearn sudah terinstal secara sah. Harap hapus file install.php demi keamanan data Anda.");
    }
}

$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['install'])) {
    $db_host = $_POST['db_host'] ?? '';
    $db_user = $_POST['db_user'] ?? '';
    $db_pass = $_POST['db_pass'] ?? '';
    $db_name = $_POST['db_name'] ?? '';

    $admin_name = $_POST['admin_name'] ?? '';
    $admin_username = $_POST['admin_username'] ?? '';
    $admin_email = $_POST['admin_email'] ?? '';
    $admin_password = $_POST['admin_password'] ?? '';

    if (empty($db_host) || empty($db_user) || empty($db_name) || empty($admin_name) || empty($admin_username) || empty($admin_password)) {
        $error = "Mohon lengkapi semua field yang wajib diisi.";
    } else {
        // Step 1: Connect to MySQL
        $conn = @new mysqli($db_host, $db_user, $db_pass);
        
        if ($conn->connect_error) {
            $error = "Koneksi Database Gagal: " . $conn->connect_error;
        } else {
            // Step 2: Attempt to create database if not exists
            $conn->query("CREATE DATABASE IF NOT EXISTS `" . $conn->real_escape_string($db_name) . "`");
            
            if (!$conn->select_db($db_name)) {
                $error = "Berhasil terhubung ke MySQL, tapi gagal memilih atau membuat database '$db_name'. Pastikan user Anda memiliki hak akses untuk database ini.";
            } else {
                // Step 3: Update config.php
                $configPath = __DIR__ . '/core/config.php';
                if (is_writable($configPath) || is_writable(dirname($configPath))) {
                    $configContent = file_get_contents($configPath);
                    $configContent = preg_replace("/\\\$db_host\s*=\s*'.*?';/", "\$db_host = '" . str_replace("'", "\'", $db_host) . "';", $configContent);
                    $configContent = preg_replace("/\\\$db_user\s*=\s*'.*?';/", "\$db_user = '" . str_replace("'", "\'", $db_user) . "';", $configContent);
                    $configContent = preg_replace("/\\\$db_pass\s*=\s*'.*?';/", "\$db_pass = '" . str_replace("'", "\'", $db_pass) . "';", $configContent);
                    $configContent = preg_replace("/\\\$db_name\s*=\s*'.*?';/", "\$db_name = '" . str_replace("'", "\'", $db_name) . "';", $configContent);
                    
                    if (file_put_contents($configPath, $configContent) === false) {
                        $error = "Gagal menyimpan konfigurasi ke core/config.php. Periksa izin file (permissions).";
                    }
                } else {
                    $error = "File core/config.php tidak dapat ditulisi (not writable). Silakan ubah permissions terlebih dahulu.";
                }

                if (empty($error)) {
                    // Step 4: Execute db_setup.sql
                    $conn->set_charset("utf8mb4");
                    $sqlPath = __DIR__ . '/db_setup.sql';
                    $sqlContent = file_get_contents($sqlPath);
                    
                    if ($conn->multi_query($sqlContent)) {
                        while ($conn->more_results() && $conn->next_result()) {;} // clear results
                    }
                    
                    // Step 5: Insert First Admin
                    $name_clean = $conn->real_escape_string($admin_name);
                    $username_clean = $conn->real_escape_string($admin_username);
                    $email_clean = $admin_email ? "'" . $conn->real_escape_string($admin_email) . "'" : "NULL";
                    $password_hashed = password_hash($admin_password, PASSWORD_BCRYPT);
                    $role = 'teacher';

                    $conn->query("INSERT INTO users (name, username, email, password, role) VALUES ('$name_clean', '$username_clean', $email_clean, '$password_hashed', '$role')");
                    
                    // Step 6: Create lock file
                    file_put_contents(__DIR__ . '/core/installed.lock', 'Installed on ' . date('Y-m-d H:i:s'));
                    
                    $success = true;
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalasi BitLearn</title>
    <!-- We must use relative path safely since we aren't using config BASE_URL yet -->
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.8/css/line.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: var(--bg-body);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
            min-height: 100vh;
        }
        .install-container {
            max-width: 800px;
            width: 100%;
        }
        .section-title {
            margin-top: 1.5rem;
            margin-bottom: 1rem;
            font-size: 1.25rem;
            font-weight: 600;
            border-bottom: 2px solid var(--border-color);
            padding-bottom: 0.5rem;
        }
    </style>
</head>
<body>

<div class="install-container">
    <div class="glass-card" style="padding: 2rem;">
        <div style="text-align: center; margin-bottom: 2rem;">
            <img src="assets/logo.png" alt="BitLearn" style="max-width: 250px; margin-bottom: 1rem; filter: drop-shadow(0 4px 6px rgba(0,0,0,0.5)); transform: scale(1.1);">
            <h1>Setup BitLearn</h1>
            <p class="text-muted">Selamat datang! Silakan lengkapi form di bawah ini untuk memulai instalasi pada hosting Anda.</p>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success mt-4">
                <i class="uil uil-check-circle"></i> Instalasi berhasil! Aplikasi BitLearn sudah siap digunakan.
            </div>
            <div class="alert alert-warning mt-2">
                <i class="uil uil-exclamation-triangle"></i> <strong>Sangat Penting:</strong> Harap hapus file <code>install.php</code> dari server Anda demi keamanan sebelum aplikasi digunakan.
            </div>
            <div style="text-align: center; margin-top: 2rem;">
                <a href="index.php" class="btn btn-primary" style="font-size: 1.1rem; padding: 0.75rem 2rem;">Menuju Halaman Login</a>
            </div>
        <?php else: ?>
        
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger" style="margin-bottom: 1.5rem;">
                    <i class="uil uil-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <h3 class="section-title">Konfigurasi Database</h3>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">Database Host *</label>
                        <input type="text" name="db_host" class="form-control" value="<?php echo htmlspecialchars($_POST['db_host'] ?? 'localhost'); ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Nama Database *</label>
                        <input type="text" name="db_name" class="form-control" value="<?php echo htmlspecialchars($_POST['db_name'] ?? 'bitlearn_db'); ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Database User *</label>
                        <input type="text" name="db_user" class="form-control" value="<?php echo htmlspecialchars($_POST['db_user'] ?? 'root'); ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Database Password</label>
                        <input type="text" name="db_pass" class="form-control" value="<?php echo htmlspecialchars($_POST['db_pass'] ?? ''); ?>">
                        <small class="text-muted">Kosongkan jika tidak ada password.</small>
                    </div>
                </div>

                <h3 class="section-title">Pengaturan Akun Admin / Guru Pertama</h3>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">Nama Lengkap *</label>
                        <input type="text" name="admin_name" class="form-control" placeholder="Contoh: Budi Santoso" value="<?php echo htmlspecialchars($_POST['admin_name'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Username *</label>
                        <input type="text" name="admin_username" class="form-control" placeholder="Contoh: admin" value="<?php echo htmlspecialchars($_POST['admin_username'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" name="admin_email" class="form-control" placeholder="admin@sekolah.com" value="<?php echo htmlspecialchars($_POST['admin_email'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Password *</label>
                        <input type="password" name="admin_password" class="form-control" placeholder="••••••••" required>
                    </div>
                </div>

                <div style="margin-top: 2rem; display: flex; justify-content: flex-end;">
                    <button type="submit" name="install" class="btn btn-primary" style="padding: 0.75rem 2rem; font-size: 1.1rem;">
                        Mulai Instalasi <i class="uil uil-arrow-right"></i>
                    </button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
